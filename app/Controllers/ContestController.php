<?php

namespace Controllers;

use \Models\Contest;
use \Models\User;

use \Util\Util;

class ContestController extends Controller {

	public function index($f3, $params) {
		$f3->mset([
			'contests' => (new Contest())->select('name, slug'),
			'title' => 'Contest List',
			'content' => $f3->get('THEME') . '/views/contests/index.html'
		]);
        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
	}

	public function show($f3, $params) {
		$contest = new Contest();
		$contest->load(['slug = ?', $params['slug']]);

		if ($contest->dry())
			$f3->error(404);

		$f3->mset([
			'title' => $contest->name,
			'contest' => $contest,
			'content' => $f3->get('THEME') . '/views/contests/show.html',
			'headPartials' => ['common/views/partials/katex-head.html'],
			'bodyPartials' => ['common/views/partials/katex-body.html']
		]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
	}

	public function enter($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);

		$contest = new Contest();
		$contest->load(['slug = ?', $params['slug']]);
		if ($contest->dry())
			$f3->error(404);

		$user = new User();
		$user->load(['id = ?', $f3->get('SESSION.user.id')]);
		$contest->addUser($user);

		$f3->set('SESSION.contest.id', $contest->id);
		$f3->set('SESSION.contest.name', $contest->name);
		$f3->set('SESSION.contest.slug', $contest->slug);
		$f3->reroute('/problems');
	}

	public function leave($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);
		$f3->clear('SESSION.contest');
		$f3->reroute('/');
	}

}
