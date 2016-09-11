<?php

namespace Models;

use \Models\Problems;
use \Models\User;

class Contest extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'contests');
	}

	public function getProblems() {
		return (new Problem())->find(['contest_id = ?', $this->id]);
	}

	public function addUser(User $user) {
		$user->current_content_id = $this->id;

		$f3->get('DB')->exec(
			'INSERT INTO users_entered_contests(user_id, contest_id)
			VALUES(:user_id, :contest_id)',
			[
				':user_id' => $user->id, 
				':contest_id' => $contest->id
			]
		);
	}

}
