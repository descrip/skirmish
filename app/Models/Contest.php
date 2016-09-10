<?php

namespace Models;

use \Models\Problems;

class Contest extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'contests');
	}

	public function getProblems() {
		return (new Problem())->find(['contest_id = ?', $this->id]);
	}

}
