<?php

namespace Models;

use \Models\Submission;
use \Models\Testcase;
use \Models\Verdict;

class Result extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'results');
	}

	public function getSubmission() {
		return (new Submission())->findone(['id = ?', $this->submission_id]);
	}

	public function getTestcase() {
		return (new Testcase())->findone(['id = ?', $this->testcase_id]);
	}

	public function getVerdict() {
		return (new Verdict())->findone(['id = ?', $this->verdict_id]);
	}

}
