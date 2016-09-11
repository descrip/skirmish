<?php

namespace Controllers;

use \Models\Problem;
use \Models\Contest;

class ProblemController extends Controller {

	public function index($f3, $params) {
		$isInContest = $f3->exists('SESSION.contest');

		if ($isInContest) {
			$contest = new Contest();
			$contest->load(['slug = ?', $f3->get('SESSION.contest')]);
			if ($contest->dry())
				$f3->error(404);
		}

		$f3->mset([
			'problems' => (new Problem())->select(
				'name, slug',
				($isInContest ? 
					['contest_id = ?', $contest->id] : 
					'contest_id IS NULL'
				)
			),
			'title' => 'Problem List',
			'content' => 'problems/index.html'
		]);
		echo(\Template::instance()->render('layout.html'));
	}

	public function show($f3, $params) {
		$problem = new Problem();
		$problem->load(['slug = ?', $params['slug']]);
		if ($problem->dry())
			$f3->error(404);

		// Make sure the problem that is being accessed is within the current contest.
		if (!is_null($problem->contest_id)) {
			$contest = new Contest();
			$contest->load(['id = ?', $problem->contest_id]);

			if ($contest->dry())
				$f3->error(404);
			else if (!$f3->exists('SESSION.contest') || $contest->slug != $f3->get('SESSION.contest'))
				$f3->error(403);
		}
		// If the problem does not belong to a contest but the user is in one, also deny access.
		else if ($f3->exists('SESSION.contest')) $f3->error(403);

		$f3->mset([
			'title' => $problem->name,
			'problem' => $problem,
			'content' => 'problems/show.html',
			'headPartials' => ['partials/katex-head.html'],
			'bodyPartials' => ['partials/katex-body.html']
		]);

		echo(\Template::instance()->render('layout.html'));
	}

}
