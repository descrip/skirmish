<?php

namespace Models;

class Submission extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'submissions');
	}

}
