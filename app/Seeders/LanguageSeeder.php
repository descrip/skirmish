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
        $cpp11->compile_command = 'g++ -std=c++11 -O2 {{ filename }}.cpp -o {{ filename }}';
		$cpp11->execute_command = './{{ filename }} < {{ filename }}.in';
		$cpp11->save();

		$py3 = new Language();
		$py3->name = 'Python 3';
		$py3->extension = 'py';
		$py3->version = 'python3 3.5.2';
		$py3->execute_command = 'python3 {{ filename }}.py < {{ filename }}.in';
		$py3->save();
	}

}
