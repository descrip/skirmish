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

		// Query subtasks with raw SQL so no ORM classes are used.
		$subtasks = $f3->get('DB')->exec(
			'SELECT id FROM subtasks WHERE problem_id = ?',
			$problem->id
		);
		for ($i = 0; $i < count($subtasks); $i++)
			$subtasks[$i]['testcases'] = $f3->get('DB')->exec(
				'SELECT id, input, output FROM testcases WHERE subtask_id = ?',
				$subtasks[$i]['id']
			);

		$f3->get('pheanstalk')->useTube('run-submission')->put(json_encode([
			'submission_id' => $submission->id,
			'compile_command' => $language->compile_command,
			'execute_command' => $language->execute_command,
			'extension' => $language->extension,
			'subtasks' => $subtasks,
			'code' => file_get_contents($f3->get('FILES.solution.tmp_name'))
		]));
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
