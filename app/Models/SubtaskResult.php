<?php

namespace Models;

use \Models\Submission;
use \Models\Subtask;
use \Models\TestcaseResult;

class SubtaskResult extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'subtask_results');
	}

	public function getSubmission() {
		return (new Submission())->findone(['id = ?', $this->submission_id]);
	}

	public function getSubtask() {
		return (new Subtask())->findone(['id = ?', $this->subtask_id]);
	}

	public function getTestcaseResults() {
		return (new TestcaseResult())->find(['subtask_result_id = ?', $this->id]);
	}

}
