<?php

namespace Util;

class Validate extends \Prefab {

    public static function isAlphaNumeric($str) {
        return !preg_match('/[^a-z0-9]/i', $str);
    }

    public static function isAlphaNumericDash($str) {
        return !preg_match('/[^a-z_\-0-9]/i', $str);
    }

    public static function isValidEmail($email){ 
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isUnique($f3, $table, $col, $what) {
        // Only allow certain hardcoded tables since PDO security does not allow table, column parameters.
        $allowedTables = ['users'];
        $allowedColumns = ['username', 'email'];

        if (in_array($table, $allowedTables) && in_array($col, $allowedColumns)) {
            $ret = $f3->get('DB')->exec(
                'SELECT 1 FROM ' . $table . ' WHERE ' . $col . ' = ?', 
                $what
            );
            return empty($ret);
        }
        else $f3->error(500);
    }

}
