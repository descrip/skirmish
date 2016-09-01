<?php

namespace Models;

use \Models\Problems;
use \Models\SubtaskResult;
use \Models\User;

class Submission extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'submissions');
	}

	public function getProblem() {
		return (new Problem())->findone(['id = ?', $this->problem_id]);
	}

	public function getSubtaskResults() {
		return (new SubtaskResult())->find(['submission_id = ?', $this->id]);
	}

	public function getUser() {
		return (new User())->findone(['id = ?', $this->user_id]);
	}

}
