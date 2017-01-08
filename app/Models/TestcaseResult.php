<?php

namespace Models;

use \Models\SubtaskResult;

class TestcaseResult extends \DB\SQL\Mapper {

    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'testcase_results');
    }

    public function getSubtaskResult() {
        return (new SubtaskResult())->findone(['id = ?', $this->subtask_result_id]);
    }

    public function getTestcase() {
        return (new Testcase())->findone(['id = ?', $this->testcase_id]);
    }

    public function getVerdict() {
        return (new Verdict())->findone(['id = ?', $this->verdict_id]);
    }

}
