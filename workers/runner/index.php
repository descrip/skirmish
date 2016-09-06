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
		'pshved_timeout' => $config['path_to_pshved_timeout_script'],
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
				$config['timeout_command'], 
				$data['execute_command'],
				contextify('< {{ filename }}.in', $context),
				'echo $?'
			),
			$context
		)
	);

	$stmt = $db->prepare('
		INSERT INTO testcase_results(subtask_result_id, testcase_id, verdict_id)
		VALUES (:subtask_result_id, :testcase_id, :verdict_id)
	');
	$stmt->bindParam(':subtask_result_id', $subtask_result_id);
	$stmt->bindParam(':testcase_id', $testcase_id);
	$stmt->bindParam(':verdict_id', $verdict_id);

	foreach ($data['subtasks'] as $subtask) {

		$db->exec(sprintf(
			'INSERT INTO subtask_results(submission_id, subtask_id)
			VALUES (%d, %d)',
			$data['submission_id'],
			$subtask['id']
		));
		$subtask_result_id = $db->lastInsertId();

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
				fputs($pipes[0], $testcase['input']);
				fclose($pipes[0]);

				while ($f = fgets($pipes[1]))
					array_push($output, $f);
				fclose($pipes[1]);

				while ($f = fgets($pipes[2]))
					$error .= $f;
				fclose($pipes[2]);
			}

			$exitCode = intval(array_pop($output));
			$limitData = explode(' ', array_pop($output));
			$output = implode("\n", $output);

			/*
			echo($exitCode);
			var_dump($limitData);
			echo($output);
			echo('-------------------');
			 */

			/* TLE check.
			 * 124: bash/timeout status code if TLE (real time).
			 * 142: 128+SIGALRM.
			 * $limitData[0] == 'TIMEOUT' (cpu+sys timeout).
			 */
			if (in_array($exitCode, [124, 142, 143]) || $limitData[0] == 'TIMEOUT')
				$verdict_id = 4;
			// MLE check.
			else if ($limitData[0] == 'MEM')
				$verdict_id = 5;
			// RE check.
			else if ($exitCode != 0)
				$verdict_id = 3;
			// WA check.
			else if (trim_output($output) == trim_output($testcase['output']))
				$verdict_id = 6;
			// Therefore AC.
			else $verdict_id = 7;

			$stmt->execute();
		}
	}

	//echo('done');
	$queue->delete($job);

}
