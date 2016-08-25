<?php

namespace Controllers;

use Models\Problem;

class ProblemController {

	public static function list($f3, $params) {
		//
	}

	public static function show($f3, $params) {
		$problem = new Problem();
		$problem->load(['@slug = ?', $params['slug']]);
		$f3->set('problem', $problem);
		$f3->set('content', 'problems/show.html');
		echo(\Template::instance()->render('layout.html'));
	}

}
