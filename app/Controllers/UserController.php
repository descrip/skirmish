<?php

namespace Controllers;

use \Models\User;
use \Models\Problem;

use \Util\Validate;

class UserController extends Controller {

	public function register($f3, $params) {
		$this->generateCsrf($f3, $params);

		$f3->mset([
			'title' => 'Register',
			'content' => $f3->get('THEME') . '/views/users/register.html'
		]);

		echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
	}

	public function create($f3, $params) {
		if (!$this->checkCsrf($f3, $params))
			$f3->error(403);

		$user = new User();
		$user->username = $f3->get('POST.username');
		$user->email = $f3->get('POST.email');
		$user->password = password_hash(
			$f3->get('POST.password'),
			PASSWORD_DEFAULT
		);
		$user->save();
		$f3->reroute('/');
	}
	
	public function login($f3, $params) {
		$this->generateCsrf($f3, $params);

		$f3->mset([
			'title' => 'Login',
			'content' => $f3->get('THEME') . '/views/users/login.html'
		]);

		echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
	}

	public function authenticate($f3, $params) {
		if (!$this->checkCsrf($f3, $params))
			$f3->error(403);

		$email = $f3->get('POST.email');
		$password = $f3->get('POST.password');

        $formErrors = [];

		$user = new User();
		$user->load(['email = ?', $email]);

        if ($password === "")
            $formErrors['password'] = "The password field is required.";

        if ($email === "")
            $formErrors['email'] = "The email field is required.";
        else if (!Validate::isValidEmail($email))
            $formErrors['email'] = "The email supplied is not a valid email address.";
        else if ($user->dry() || ($password !== "" && !password_verify($password, $user->password)))
            $formErrors['common'] = "The email and the password do not match.";
            
        if (empty($formErrors)) {
			$f3->set('SESSION.user.id', $user->id);
			$f3->set('SESSION.user.username', $user->username);
			$f3->reroute('/');
		}
        else {
            $f3->set('formErrors', $formErrors);
            $this->login($f3, $params);
        }
	}

	public function logout($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);
		$f3->clear('SESSION');
		$f3->reroute('/');
	}

	public function leaderboard($f3, $params) {
		$users = new User();

		if (!$f3->exists('SESSION.contest')) {
			$users = $users->select(
				'id, username, points',
				NULL,
				[ 'order' => 'points DESC' ]
			);

			$f3->mset([
				'users' => $users,
				'title' => 'Leaderboards',
				'content' => $f3->get('THEME') . '/views/users/leaderboard.html'
			]);

			echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
		}
		else {
		}
	}

    public function show($f3, $params) {
        $user = new User();
        $user->load(['username = ?', $params['username']]);
        if ($user->dry())
            $f3->error(404);

        $rank = 1 + $user->count([
            '(points > :points) OR (points = :points AND username < :username)',
            ':points' => $user->points,
            ':username' => $user->username
        ]);

        $problemsAttempted = $f3->get('DB')->exec(
            'SELECT problems.slug AS problem_slug, problems.name AS problem_name, problems.points AS problem_points, submissions.id AS best_submission_id, submissions.points AS best_submission_points
            FROM users_solved_problems_pivot
            LEFT JOIN problems ON users_solved_problems_pivot.problem_id = problems.id
            LEFT JOIN submissions ON users_solved_problems_pivot.best_submission_id = submissions.id
            WHERE users_solved_problems_pivot.user_id = ?
            ORDER BY problems.name ASC',
            $user->id
        );

        $f3->mset([
            'user' => $user,
            'userRank' => $rank,
            'problemsAttempted' => $problemsAttempted,
            'title' => $user->username . '\'s Profile',
            'content' => $f3->get('THEME') . '/views/users/show.html'
        ]);

        echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
    }

}
