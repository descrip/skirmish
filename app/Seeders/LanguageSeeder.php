<?php

namespace Seeders;

use Models\Language;

class LanguageSeeder {

	public static function seed($f3) {
		$db = $f3->get('DB');
		$db->exec('DELETE FROM languages');
		$db->exec('VACUUM');

		$cpp11 = new Language();
		$cpp11->name = "C++11";
		$cpp11->version = "g++ 5.4.0";
		$cpp11->save();

		$py3 = new Language();
		$py3->name = "Python 3";
		$py3->version = "python3 3.5.2";
		$py3->save();
	}

}
