<?php

namespace Controllers;

use Models\User;
use Models\Problem;

class UserController extends Controller {
	
	public function login($f3, $params) {
		$f3->set('title', 'Login');
		$f3->set('content', 'users/login.html');
		$f3->set('loadKatex', false);
		echo(\Template::instance()->render('layout.html'));
	}

	public function authenticate($f3, $params) {
		$email = $f3->get('POST.email');
		$password = $f3->get('POST.password');

		$user = new User();
		$user->load(['email = ?', $email]);

		if ($user->dry())
			$f3->reroute('/login');
		else if (password_verify($password, $user->password)) {
			$f3->set('SESSION.user', $user->username);
			$f3->reroute('/');
		}
		else $f3->reroute('/login');
	}

	public function new($f3, $params) {
		$f3->set('title', 'Register');
		$f3->set('content', 'users/register.html');
		$f3->set('loadKatex', false);
		echo(\Template::instance()->render('layout.html'));
	}

	public function create($f3, $params) {
		$user = new User();
		$user->username = $f3->get('POST.username');
		$user->email = $f3->get('POST.email');
		$user->password = password_hash($f3->get('POST.password'), PASSWORD_DEFAULT);
		$user->save();
		$f3->reroute('/');
	}
}
