<?php

namespace Models;

use \Models\Problem;
use \Models\Tescases;

class Subtask extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'subtasks');
	}

	public function getProblem() {
		return (new Problem())->findone(['id = ?', $this->problem_id]);
	}

	public function getTestcases() {
		return (new Testcase())->find(['subtask_id = ?', $this->id]);
	}

}
