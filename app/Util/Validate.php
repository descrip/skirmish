<?php

namespace Util;

class Validate extends \Prefab {

    public static function isAlphaNumeric($str) {
        return !preg_match('/[^a-z0-9]/i', $str);
    }

    public static function isAlphaNumericDash($str) {
        return !preg_match('/[^a-z_\-0-9]/i', $str);
    }

    /*
    public static function isAlphaNumericDash($str) {
        return preg_match('/^[a-zA-Z0-9-_]+$/', $str);
    }
     */

}
