<?php

require(__DIR__ . '/vendor/autoload.php');
use Pheanstalk\Pheanstalk;

function contextify($str, $arr) {
	preg_match_all('/{{ [a-zA-Z_]+ }}/', $str, $matches);
	$matches[0] = array_unique($matches[0]);
	foreach ($matches[0] as $match) {
		$var = substr($match, 3, strlen($match)-6);
		$str = str_replace($match, $arr[$var], $str);
	}
	return $str;
}

function trim_output($str) {
	return trim(preg_replace('/\r\n|\n|\r/', '\n', $str));
}

$config = parse_ini_file(__DIR__ . '/config.ini', true);

$db = new PDO(
	sprintf('mysql:host=%s;port=%d;dbname=%s',
		$config['mysql']['host'],
		$config['mysql']['port'],
		$config['mysql']['database']
	),
	$config['mysql']['user'],
	$config['mysql']['password']
);

$queue = new Pheanstalk($config['beanstalkd']['host']);
$queue->watch('run-submission');

while ($job = $queue->reserve()) {
	$data = json_decode($job->getData(), true);

	$context = [
		'cwd' => __DIR__,
		'sandbox_dir' => $config['sandbox_directory'],
		'filename' => $config['sandbox_directory'] . $data['problem_slug'],
	];

	@file_put_contents(
		$context['filename'] . '.' . $data['extension'],
		$data['code']
	);

	$context['time_limit'] = $data['execution_time_limit'];
	$context['memory_limit'] = $data['execution_memory_limit'];
	@file_put_contents(
		$context['filename'] . '.sh',
		contextify(
			sprintf("%s %s %s 2>&1\n%s",
				$config['limit_command'], 
				$data['execute_command'],
				contextify('< {{ filename }}.in', $context),
				'echo $?'
			),
			$context
		)
	);

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

			@file_put_contents(
				$context['filename'] . '.in',
				$testcase['input']
			);

			$process = proc_open(
				sprintf('%s sh %s.sh', 
					contextify($config['sandbox_command'], $context),
					$context['filename']
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

			$exitCode = intval(array_pop($output));
			$output = implode("\n", $output);

			/*
			echo($exitCode . "\n");
			echo($output . "\n");
			echo('-------------------');
			 */

			/* TLE check.
			 * 124: bash/timeout status code if TLE (real time).
			 */
			if ($exitCode == 124) $verdict_id = 4;
			// RE check.
			else if ($exitCode != 0) $verdict_id = 5;
			// WA check.
			else if (trim_output($output) != trim_output($testcase['output']))
				$verdict_id = 3;
			// Therefore AC.
			else $verdict_id = 2;

			$stmt->execute();
		}
	}

	//echo("done\n");
	$queue->delete($job);

}
