<?php

namespace Controllers;

use \Models\Submission;
use \Models\Result;
use \Models\Problem;
use \Models\Language;

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

		ob_end_clean();
		header("Connection: close");
		ignore_user_abort(true);

		//$f3->reroute('/');
		echo('okay');

		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();                   // Strange behavior; this will not work unless..
		flush();                          // both functions are called !

		sleep(5);

		$problem = new Problem();
		$problem->load(['slug = ?', 'aplusb']);
		$problem->name = "A Plus BBBB";
		$problem->save();
	}

}
