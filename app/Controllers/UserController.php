<?php

namespace Controllers;

use \Models\User;
use \Models\Problem;

class UserController extends Controller {

	public function new($f3, $params) {
		$this->generateCsrf($f3, $params);
		$f3->mset([
			'title' => 'Register',
			'content' => 'users/new.html'
		]);
		echo(\Template::instance()->render('layout.html'));
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
			'content' => 'users/login.html'
		]);
		echo(\Template::instance()->render('layout.html'));
	}

	public function authenticate($f3, $params) {
		if (!$this->checkCsrf($f3, $params))
			$f3->error(403);

		$email = $f3->get('POST.email');
		$password = $f3->get('POST.password');

		$user = new User();
		$user->load(['email = ?', $email]);

		if ($user->dry())
			$f3->reroute('/login');
		else if (password_verify($password, $user->password)) {
			$f3->set('SESSION.user.id', $user->id);
			$f3->set('SESSION.user.username', $user->username);
			$f3->reroute('/');
		}
		else $f3->reroute('/login');
	}

	public function logout($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);
		$f3->clear('SESSION');
		$f3->reroute('/');
	}

	public function index($f3, $params) {
		$users = new User();

		if (!$f3->exists('SESSION.contest')) {
			$users = $users->select(
				'id, username, points',
				NULL,
				[ 'order' => 'points DESC' ]
			);

			$f3->mset([
				'users' => $users,
				'title' => 'User Leaderboards',
				'content' => 'users/index.html'
			]);

			echo(\Template::instance()->render('layout.html'));
		}
		else {
		}
	}

}
