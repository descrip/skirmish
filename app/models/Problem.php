<?php

namespace models;

class Problem extends \DB\Jig\Mapper {
	public function __construct() {
		parent::__construct(\Base::instance()->get('DB'), 'problems.json');
	}
}
