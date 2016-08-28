<?php

namespace Controllers;

use Models\Problem;

class ProblemController extends Controller {

	public function index($f3, $params) {
		$f3->set('title', 'Problem List');

		$problem = new Problem();
		$f3->set('problems', $problem->select("id, name, slug", NULL, [ 'order' => 'id ASC' ]));

		$f3->set('content', 'problems/list.html');
		$f3->set('loadKatex', true);

		echo(\Template::instance()->render('layout.html'));
	}

	public function show($f3, $params) {
		$problem = new Problem();
		$problem->load(['slug = ?', $params['slug']]);

		if ($problem->dry())
			$f3->error(404);

		$f3->set('title', $problem->name);
		$f3->set('problem', $problem);
		$f3->set('content', 'problems/show.html');

		echo(\Template::instance()->render('layout.html'));
	}

}
