<?php

namespace Util;

class Util extends \Prefab {

    public static function getMySqlTimestamp($time) {
        return date('Y-m-d G:i:s', $time);
    }

    /*
    public static function generateProblemCodes($len, $style) {
        $ret = [];
        for ($i = 0; $i < $len; $i++) {
            $str = $style;
            // Replace @ and # where there's an even number of \ before.
            // i.e. Only match unescaped @'s and #'s.
            $str = preg_replace($str, '/(?<!\\)((?:\\\\)*)@/', (char)('A'+$i));
            $str = preg_replace($str, '/(?<!\\)((?:\\\\)*)#/', $i);
            array_push($ret, $str);
        }
        return $ret;
    }
     */

}
