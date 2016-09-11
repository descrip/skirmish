<?php

namespace Controllers;

use \Models\Contest;
use \Models\User;

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

	public function enter($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);

		$contest = new Contest();
		$contest->load(['slug = ?', $params['slug']]);
		if ($contest->dry())
			$f3->error(404);

		$user = new User();
		$user->load(['username = ?', $f3->get('SESSION.user')]);
		if ($user->dry())
			$f3->error(403);

		$f3->set('SESSION.contest', $contest->slug);

		$f3->reroute('/problems');
	}

}
