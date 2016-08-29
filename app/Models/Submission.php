<?php

namespace Models;

use \Models\Problems;

class Submission extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'submissions');
	}

	public function getProblem() {
		return (new Problem())->findone(['slug = ?', $this->problem_slug]);
	}

	public function getResults() {
		return (new Result())->find(['submission_id = ?', $this->id]);
	}

	public function getUser() {
		return (new User())->findone(['username = ?', $this->user_username]);
	}

}
