<?php

namespace Controllers;

use \Models\Submission;
use \Models\Result;
use \Models\Problem;
use \Models\Language;
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
			'problems' => $problem->select('name, slug'),
			'languages' => $language->select('id, name, version')
		]);

		echo(\Template::instance()->render('layout.html'));
	}

	public function create($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);
		if (!$this->checkCsrf($f3, $params))
			$f3->error(403);

		/* 
		 * Hacky client detach to run this in the background.
		 * http://www.php.net/manual/en/features.connection-handling.php#71172.
		 */
		ob_end_clean();
		header("Connection: close");
		ignore_user_abort(true);

		$f3->mset([
			'headPartials' => ['partials/meta-refresh.html'],
			'metaRefreshUrl' => '/problems/aplusb',
			'content' => 'submissions/redirect.html'
		]);
		echo(\Template::instance()->render('layout.html'));

		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();
		flush();

		sleep(5);

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
