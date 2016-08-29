<?php

namespace Models;

class Result extends \DB\SQL\Mapper {

	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'results');
	}

}
