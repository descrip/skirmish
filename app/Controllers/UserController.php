<?php

namespace Controllers;

use Models\User;
use Models\Problem;

class UserController {

	public static function submit($f3, $params) {
		$f3->set('title', 'Submit');
		$f3->set('content', 'submit.html');
		$problem = new Problem();
		$f3->set('problems',$problem->find(NULL, ['order' => '_id SORT_ASC']));
		echo(\Template::instance()->render('layout.html'));
	}

}
