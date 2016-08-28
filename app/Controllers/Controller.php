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
		$f3->clear('loadKatex');
	}

	public function checkIfAuthenticated($f3, $params) {
		if (!$f3->get('SESSION.user'))
			$f3->reroute('/login');
	}

}
