<?php

namespace Controllers;

use Models\Problem;

class ProblemController {

	public static function list($f3, $params) {
		$problems = new Problem();
		$f3->set('problems', $problems->find(NULL, ['order' => '_id SORT_ASC']));
		$f3->set('content', 'problems/list.html');
		echo(\Template::instance()->render('layout.html'));
	}

	public static function show($f3, $params) {
		$problem = new Problem();
		$problem->load(['@slug = ?', $params['slug']]);
		$f3->set('problem', $problem);
		$f3->set('content', 'problems/show.html');
		echo(\Template::instance()->render('layout.html'));
	}

}
