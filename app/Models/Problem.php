<?php

namespace Models;

use \Models\Subtask;
use \Models\Submission;

class Problem extends \DB\SQL\Mapper {

    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'problems');
    }

    public function getSubmissions() {
        return (new Submission())->find(['problem_id = ?', $this->id]);
    }

    public function getSubtasks() {
        return (new Subtask())->find(['problem_id = ?', $this->id]);
    }

}
