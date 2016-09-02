<?php

namespace Seeders;

use \Models\User;

class UserSeeder {
	
	public static function seed($f3) {
		$db = $f3->get('DB');
		$db->exec('DELETE FROM users');

		$test = new User();
		$test->username = 'test';
		$test->email = 'test@example.com';
		$test->password = password_hash('secret', PASSWORD_DEFAULT);
		$test->save();
	}

}
