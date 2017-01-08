<?php

namespace Models;

class Language extends \DB\SQL\Mapper {

    public function __construct() {
        parent::__construct(\Base::instance()->get('DB'), 'languages');
    }

}
