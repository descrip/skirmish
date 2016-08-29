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
			'content' => 'submit.html',
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
		 * Hacky client detach to make this function run as a background process from here on.
		 * Taken from http://www.php.net/manual/en/features.connection-handling.php#71172.
		 */
		ob_end_clean();
		header("Connection: close");
		ignore_user_abort(true);

		// TODO: Add submission view here.

		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();                   // Strange behavior; this will not work unless...
		flush();                          // Both functions are called !

		sleep(5);

		/*
		$problem = new Problem();
		$problem->load(['slug = ?', 'aplusb']);
		$problem->name = "A Plus B";
		$problem->save();
		*/
	}

}
