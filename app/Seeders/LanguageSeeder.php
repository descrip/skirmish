<?php

namespace Seeders;

use \Models\Language;

class LanguageSeeder {

	public static function seed($f3) {
		$db = $f3->get('DB');
		$db->exec('DELETE FROM languages');

		$cpp11 = new Language();
		$cpp11->name = 'C++11';
		$cpp11->version = 'g++ 5.4.0';
		$cpp11->extension = 'cpp';
		$cpp11->execute_command = '';
		$cpp11->save();

		$py3 = new Language();
		$py3->name = 'Python 3';
		$py3->extension = 'py';
		$py3->version = 'python3 3.5.2';
		$py3->execute_command = 'python3 {{ filename }}.py';
		$py3->save();
	}

}
