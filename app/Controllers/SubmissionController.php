<?php

namespace Controllers;

//use Models\Submission;
use Models\Problem;
use Models\Language;

class SubmissionController extends Controller {

	public function new($f3, $params) {
		$f3->set('title', 'Submit');
		$f3->set('content', 'submit.html');
		$problem = new Problem();
		$f3->set('problems', $problem->select('id, name, slug'));
		$language = new Language();
		$f3->set('languages', $language->select('id, name, version'));
		echo(\Template::instance()->render('layout.html'));
	}

	public function create($f3, $params) {
	}

}
