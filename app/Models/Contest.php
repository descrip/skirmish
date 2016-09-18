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
		\Base::instance()->get('DB')->exec(
			'INSERT IGNORE INTO users_entered_contests_pivot(user_id, contest_id)
			VALUES(:user_id, :contest_id)',
			[
				':user_id' => $user->id, 
				':contest_id' => $this->id
			]
		);
	}

}
