<?php

namespace Models;

class Problem extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'problems');
	}

}
