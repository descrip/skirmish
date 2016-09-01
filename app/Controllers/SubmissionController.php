<?php

namespace Controllers;

use \Models\Language;
use \Models\Verdict;
use \Models\User;
use \Models\Problem;
use \Models\Subtask;
use \Models\Testcase;
use \Models\Submission;
use \Models\SubtaskResult;
use \Models\TestcaseResult;
use \Controllers\ProblemController;

class SubmissionController extends Controller {

	public function new($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);
		$this->generateCsrf($f3, $params);

		$problem = new Problem();
		$language = new Language();

		$f3->mset([
			'title' => 'Submit',
			'content' => 'submissions/new.html',
			'problems' => $problem->select('id, name, slug'),
			'languages' => $language->select('id, name, version')
		]);

		echo(\Template::instance()->render('layout.html'));
	}

	public function create($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);
		if (!$this->checkCsrf($f3, $params))
			$f3->error(403);

		$problem = new Problem();
		$problem->load(['id = ?', $f3->get('POST.problemId')]);
		if ($problem->dry())
			$f3->error(404);

		$language = new Language();
		$language->load(['id = ?', $f3->get('POST.languageId')]);
		if ($language->dry())
			$f3->error(404);

		$user = new User();
		$user->load(['username = ?', $f3->get('SESSION.user')]);
		if ($user->dry())
			$f3->error(403);

		$submission = new Submission();
		$submission->problem_id = $problem->id;
		$submission->user_id = $user->id;
		$submission->language_id = $language->id;
		$submission->save();

		echo('<pre>');
		var_dump($_POST);
		echo('</pre>');

		foreach ($problem->getSubtasks() as $subtask) {
			$subtaskResult = new SubtaskResult();
			$subtaskResult->submission_id = $submission->id;
			$subtaskResult->subtask_id = $subtask->id;
			$subtaskResult->save();

			foreach ($subtask->getTestcases() as $testcase) {
				$f3->get('PHEANSTALK')
					->useTube('run-testcase')
					->put(json_encode([
						'input' => $testcase->input,
						'output' => $testcase->output,
						'compile_command' => $language->compile_command,
						'execute_command' => $language->execute_command,
						'code' => 'print("Hello World!")',
						'subtask_result_id' => $subtaskResult->id,
						'testcase_id' => $testcase->id
					]));
			}
		}
	}

	public function show($f3, $params) {
		$submission = new Submission();
		$submission->load(['id = ?', $params['id']]);

		if ($submission->dry())
			$f3->error(404);

		$f3->mset([
			'title' => 'Submission #'.$params['id'],
			'submission' => $submission,
			'content' => 'submissions/show.html'
		]);

		echo(\Template::instance()->render('layout.html'));
	}

}
