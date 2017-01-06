<?php

require(__DIR__ . '/vendor/autoload.php');
use Pheanstalk\Pheanstalk;

// Replaces {{ variable }} instances in a string.
function contextify($str, $arr) {
	preg_match_all('/{{ [a-zA-Z_]+ }}/', $str, $matches);
	$matches[0] = array_unique($matches[0]);
	foreach ($matches[0] as $match) {
		$var = substr($match, 3, strlen($match)-6);
		$str = str_replace($match, $arr[$var], $str);
	}
	return $str;
}

// Trims any ending newlines and replaces carrier returns with unix line endings.
function trim_output($str) {
	return trim(preg_replace('/\r\n|\n|\r/', '\n', $str));
}

function clearDirectory($dir) {
    // Copied from http://stackoverflow.com/a/4594262/3011359
    $files = glob($dir . '/*', GLOB_BRACE);
    foreach ($files as $file) {
      if (is_file($file))
        unlink($file);
    }
}

// Runs a command and gathers output, error, and an exit code.
function runProcess($command, $config, $data, $context, &$output, &$error, &$exitCode) {
    // Write a command to a shell script that will execute the program.
	@file_put_contents(
		$context['filepath'] . '.sh',
		contextify(
			sprintf("%s\n%s %s 2>&1\n%s",
			//sprintf("%s %s %s\n%s",
                'cd ' . $context['sandbox_dir'],
				$config['limit_command'], 
                $command,
				'echo $?'
			),
			$context
		)
	);

    // Open a process of running the program.
    $process = proc_open(
        sprintf('%s sh %s.sh', 
            contextify($config['sandbox_command'], $context),
            $context['filepath']
        ),
        [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ],
        $pipes
    );

    $output = [];
    $error = '';

    // Catch any piped output and error.
    if (is_resource($process)) {
        /*
        fputs($pipes[0], $testcase['input']);
        fclose($pipes[0]);
         */
        while ($f = fgets($pipes[1]))
            array_push($output, $f);
        fclose($pipes[1]);

        while ($f = fgets($pipes[2]))
            $error .= $f;
        fclose($pipes[2]);
    }

    // Grab any exit codes and prep the output.
    $exitCode = intval(array_pop($output));
    $output = implode("\n", $output);
}

$config = parse_ini_file(__DIR__ . '/config.ini', true);

// Open a connection to the database.
$db = new PDO(
    sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8',
        $config['mysql']['host'],
        $config['mysql']['port'],
        $config['mysql']['database']
	),
	$config['mysql']['user'],
	$config['mysql']['password']
);

// Open a Pheanstalk job queue connectoin.
$queue = new Pheanstalk($config['beanstalkd']['host']);
$queue->watch('run-submission');

// When we have a job that needs to be completed:
while ($job = $queue->reserve()) {
	$data = json_decode($job->getData(), true);

    // Build a context object for contextify().
	$context = [
		'cwd' => __DIR__,
		'sandbox_dir' => $config['sandbox_directory'],
        'filename' => $data['problem_slug'],
        'filepath' => $config['sandbox_directory'] . $data['problem_slug'],
        // Note that time_limit and memory_limit will be replaced with
        // execution specific limits a little bit down.
        'time_limit' => $config['compile_time_limit'],
        'memory_limit' => $config['compile_memory_limit']
	];

    // Write the code to a file on the machine.
	@file_put_contents(
		$context['filepath'] . '.' . $data['extension'],
		$data['code']
	);

    if ($data['compile_command']) {
        // Run a process to compile the program.
        runProcess(
            $data['compile_command'], $config, $data, $context,
            $compileOutput, $compileError, $compileExitCode
        );

        if ($compileOutput) {
            $stmt = $db->prepare('
                INSERT INTO submissions_compiler_messages
                VALUES(:submission_id, :compileOutput)
            ');
            $stmt->bindParam(':submission_id', $submission_id);
            $stmt->bindParam(':compileOutput', $compileOutput);
            $submission_id = $data['submission_id'];
            $stmt->execute();
        }
    }
    else $compileExitCode = 0;

	$context['time_limit'] = $data['execution_time_limit'];
	$context['memory_limit'] = $data['execution_memory_limit'];

    // Prepare a SQL statement to update the database with the results.
	$stmt = $db->prepare('
		UPDATE testcase_results SET verdict_id = :verdict_id
		WHERE subtask_result_id = :subtask_result_id AND testcase_id = :testcase_id
	');
	$stmt->bindParam(':subtask_result_id', $subtask_result_id);
	$stmt->bindParam(':testcase_id', $testcase_id);
	$stmt->bindParam(':verdict_id', $verdict_id);

	foreach ($data['subtasks'] as $subtask) {
		$subtask_result_id = $subtask['subtask_result_id'];
		foreach ($subtask['testcases'] as $testcase) {
			$testcase_id = $testcase['id'];

            // CE check.
            if ($compileExitCode != 0)
                $verdict_id = 6;
            else {
                // Write the current testcase to the machine.
                @file_put_contents(
                    $context['filepath'] . '.in',
                    $testcase['input']
                );

                runProcess(
                    $data['execute_command'], $config, $data, $context,
                    $executeOutput, $executeError, $executeExitCode
                );

                /* TLE check.
                 * 124: bash/timeout status code if TLE (real time).
                 */
                if ($executeExitCode == 124) $verdict_id = 4;
                // RE check.
                else if ($executeExitCode != 0) $verdict_id = 5;
                // WA check.
                else if (trim_output($executeOutput) != trim_output($testcase['output']))
                    $verdict_id = 3;
                // Therefore AC.
                else $verdict_id = 2;
            }

            // Update the database.
			$stmt->execute();
		}
	}

    clearDirectory($config['sandbox_directory']);
	$queue->delete($job);
}
