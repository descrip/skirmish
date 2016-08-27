<?php

namespace Models;

class Verdict extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'verdicts');
	}

}
