<?php

namespace Controllers;

use Models\Problem;

class ProblemController extends Controller {

	public function index($f3, $params) {
		$problem = new Problem();

		$f3->mset([
			'problems' => $problem->select('name, slug'),
			'title' => 'Problem List',
			'content' => 'problems/list.html'
		]);

		echo(\Template::instance()->render('layout.html'));
	}

	public function show($f3, $params) {
		$problem = new Problem();
		$problem->load(['slug = ?', $params['slug']]);

		if ($problem->dry())
			$f3->error(404);

		$f3->mset([
			'title' => $problem->name,
			'problem' => $problem,
			'content' => 'problems/show.html',
			'loadKatex' => true
		]);

		echo(\Template::instance()->render('layout.html'));
	}

}
