<?php

class Util {

	public static function getMySqlTimestamp($time) {
		return date('Y-m-d G:i:s', $time);
	}

}
