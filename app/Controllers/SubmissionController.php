<?php

namespace Controllers;

//use Models\Submission;
use Models\Problem;

class SubmissionController {

	public static function create($f3, $params) {
		$f3->set('title', 'Submit');
		$f3->set('content', 'submit.html');
		$problem = new Problem();
		$f3->set('problems',$problem->find(NULL, ['order' => '_id SORT_ASC']));
		echo(\Template::instance()->render('layout.html'));
	}

	public static function store($f3, $params) {
		var_dump($_POST);
	}

}
