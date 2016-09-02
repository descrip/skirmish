<?php

require(__DIR__ . '/../vendor/autoload.php');
use Pheanstalk\Pheanstalk;

$config = parse_ini_file(__DIR__ . '/../config.ini', true);
$queue = new Pheanstalk($config['beanstalkd']['host']);
$queue->watch('run-submission');

// Argument for proc_open
$descriptorspec = [
   0 => ["pipe", "r"],
   1 => ["pipe", "w"],
   2 => ["file", "/tmp/error-output.txt", "a"]
];

function compare_outputs($str1, $str2) {
	return trim(preg_replace('/\r\n|\n|\r/', '\n', $str1)) ==
		trim(preg_replace('/\r\n|\n|\r/', '\n', $str2));
}

function format_string($str, $arr) {
	preg_match_all('/{{ [a-zA-Z]+ }}/', $str, $matches);
	$matches[0] = array_unique($matches[0]);
	foreach ($matches[0] as $match) {
		$var = substr($match, 3, strlen($match)-6);
		$str = str_replace($match, $arr[$var], $str);
	}
	return $str;
}

while ($job = $queue->reserve()) {
	$data = json_decode($job->getData(), true);
	$context = [
		'directory' => __DIR__ . '/../tmp/run-submission/',
		'filename' => 'submission'
	];

	file_put_contents(
		$context['directory'] . 'submission.' . $data['extension'],
		$data['code']
	);
	if ($data['compile_command'])
		$output = exec(format_string($data['compile_command'], $context));

	foreach ($data['subtasks'] as $subtask) {
		foreach ($subtask['testcases'] as $testcase) {
			file_put_contents(
				$context['directory'] . 'submission.in',
				$testcase['input']
			);

			$process = proc_open(
				format_string($data['execute_command'], $context),
				$descriptorspec,
				$pipes
			);

			$output = '';

			if (is_resource($process)) {
				fputs($pipes[0], $testcase['input']);
				fclose($pipes[0]);

				while ($f = fgets($pipes[1]))
					$output .= $f;
				fclose($pipes[1]);
			}
		}
	}

	//$queue->delete($job);
}
