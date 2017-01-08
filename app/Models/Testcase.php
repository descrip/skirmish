<?php

namespace Models;

use \Models\Subtask;

class Testcase extends \DB\SQL\Mapper {

    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'testcases');
    }

    public function getSubtask() {
        return (new Subtask())->findone(['id = ?', $this->subtask_id]);
    }

}
