<?php

namespace Controllers;

use \Models\Problem;
use \Models\Contest;
use \Models\Submission;

use \Controllers\SubmissionController;

class ProblemController extends Controller {

    public function index($f3, $params) {
        $isInContest = $f3->exists('SESSION.contest');

        if ($isInContest) {
            $contest = new Contest();
            $contest->load(['id = ?', $f3->get('SESSION.contest.id')]);
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
            'content' => $f3->get('THEME') . '/views/problems/index.html'
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

    public function show($f3, $params) {
        $problem = new Problem();
        $problem->load(['slug = ?', rawurldecode($params['slug'])]);
        if ($problem->dry())
            $f3->error(404);

        // Make sure the problem that is being accessed is within the current contest.
        if (!is_null($problem->contest_id)) {
            $contest = new Contest();
            $contest->load(['id = ?', $problem->contest_id]);

            if ($contest->dry())
                $f3->error(404);
            else if (!$f3->exists('SESSION.contest') || $contest->name != $f3->get('SESSION.contest.name'))
                $f3->error(403);
        }
        // If the problem does not belong to a contest but the user is in one, also deny access.
        else if ($f3->exists('SESSION.contest')) $f3->error(403);

        $f3->mset([
            'title' => $problem->name,
            'problem' => $problem,
            'content' => $f3->get('THEME') . '/views/problems/show.html',
            'headPartials' => ['common/views/partials/katex-head.html'],
            'bodyPartials' => ['common/views/partials/katex-body.html']
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

    public function allSubmissions($f3, $params) {
        parse_str($f3->get('QUERY'));

        if (isset($offset)) $offset = intval($offset);
        else $offset = 0;

        if (isset($limit)) $limit = intval($limit);
        else $limit = 10;

        $problem = new Problem();
        $problem->load(['slug = ?', rawurldecode($params['slug'])]);
        if ($problem->dry())
            $f3->error(404);

        $submissions = $f3->get('DB')->exec(
            'SELECT submissions.*, users.username FROM submissions
            LEFT JOIN users ON submissions.user_id = users.id
            WHERE submissions.problem_id = :problem_id
            ORDER BY time DESC
            LIMIT :limit OFFSET :offset',
            [
                ':offset' => $offset,
                ':limit' => $limit,
                ':problem_id' => $problem->id
            ]
        );

        $f3->mset([
            'title' => 'All Submissions to ' . $problem->name,
            'problem' => $problem,
            'submissions' => $submissions,
            'content' => $f3->get('THEME') . '/views/problems/all-submissions.html'
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

    public function bestSubmissions($f3, $params) {
        parse_str($f3->get('QUERY'));

        if (isset($offset)) $offset = intval($offset);
        else $offset = 0;

        if (isset($limit)) $limit = intval($limit);
        else $limit = 10;

        $problem = new Problem();
        $problem->load(['slug = ?', rawurldecode($params['slug'])]);
        if ($problem->dry())
            $f3->error(404);

        $submissions = $f3->get('DB')->exec(
            'SELECT submissions.*, users.username FROM submissions
            LEFT JOIN users ON submissions.user_id = users.id
            LEFT JOIN verdicts ON submissions.verdict_id = verdicts.id 
            WHERE submissions.problem_id = :problem_id
            AND verdicts.is_accepted = 1
            ORDER BY submissions.points DESC, submissions.time DESC
            LIMIT :limit OFFSET :offset',
            [
                ':offset' => $offset,
                ':limit' => $limit,
                ':problem_id' => $problem->id
            ]
        );

        $f3->mset([
            'title' => 'Best Submissions to ' . $problem->name,
            'problem' => $problem,
            'submissions' => $submissions,
            'content' => $f3->get('THEME') . '/views/problems/best-submissions.html'
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

    public function yourSubmissions($f3, $params) {
        $this->checkIfAuthenticated($f3, $params);
        parse_str($f3->get('QUERY'));

        if (isset($offset)) $offset = intval($offset);
        else $offset = 0;

        if (isset($limit)) $limit = intval($limit);
        else $limit = 10;

        $problem = new Problem();
        $problem->load(['slug = ?', rawurldecode($params['slug'])]);
        if ($problem->dry())
            $f3->error(404);

        $submissions = $f3->get('DB')->exec(
            'SELECT submissions.*, users.username FROM submissions
            LEFT JOIN users ON submissions.user_id = users.id
            WHERE submissions.problem_id = :problem_id
            AND users.id = :user_id
            ORDER BY submissions.time DESC
            LIMIT :limit OFFSET :offset',
            [
                ':offset' => $offset,
                ':limit' => $limit,
                ':problem_id' => $problem->id,
                ':user_id' => $f3->get('SESSION.user.id')
            ]
        );

        $f3->mset([
            'title' => 'Your Submissions to ' . $problem->name,
            'problem' => $problem,
            'submissions' => $submissions,
            'content' => $f3->get('THEME') . '/views/problems/your-submissions.html'
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

}
