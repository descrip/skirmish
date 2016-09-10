<?php

namespace Controllers;

use \Models\Contest;

class ContestController extends Controller {

	public function index($f3, $params) {
		$f3->mset([
			'contests' => (new Contest())->select('name, slug'),
			'title' => 'Contest List',
			'content' => 'contests/index.html'
		]);
		echo(\Template::instance()->render('layout.html'));
	}

	public function show($f3, $params) {
		$contest = new Contest();
		$contest->load(['slug = ?', $params['slug']]);

		if ($contest->dry())
			$f3->error(404);

		$f3->mset([
			'title' => $contest->name,
			'contest' => $contest,
			'content' => 'contests/show.html',
			'headPartials' => ['partials/katex-head.html'],
			'bodyPartials' => ['partials/katex-body.html']
		]);

		echo(\Template::instance()->render('layout.html'));
	}

}
