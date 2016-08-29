<?php

namespace Models;

class Testcase extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'testcases');
	}

}
