<?php

namespace Models;

use \Models\Testcase;
use \Models\Submission;

class Problem extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'problems');
	}

	public function getTestcases() {
		return (new Testcase())->find(['problem_slug = ?', $this->slug]);
	}

	public function getSubmissions() {
		return (new Submission())->find(['problem_slug = ?', $this->slug]);
	}

}
