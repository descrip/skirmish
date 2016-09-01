<?php

namespace Controllers;

class Controller {

	public function __construct() {
		//
	}

	public function beforeRoute($f3, $params) {
		//
	}

	public function afterRoute($f3, $params) {
		//
	}

	public function generateCsrf($f3, $params) {
		$sess = new \DB\SQL\Session($f3->get('DB'));
		$f3->set('SESSION.csrf', $sess->csrf());
	}

	public function checkCsrf($f3, $params) {
		return ($f3->get('POST.csrf') == $f3->get('SESSION.csrf'));
	}

	public function checkIfAuthenticated($f3, $params) {
		if (!$f3->get('SESSION.user'))
			$f3->reroute('/login');
	}

}
