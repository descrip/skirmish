<?php

namespace Controllers;

class CommandController extends Controller {

	public function beforeRoute($f3, $params) {
		// 403's if controller method is not accessed from a CLI.
		if (php_sapi_name() != 'cli')
			$f3->error(403);
	}

	public function createSchema($f3, $params) {
		$result = $f3->get('DB')->exec([
			// Empty the database by dropping it and making a new one.
			// TODO: Any way to handle clearing a datbase with less privleges?
			'DROP DATABASE ' . $f3->get('mysql.database'),
			'CREATE DATABASE ' . $f3->get('mysql.database'),
			'USE ' . $f3->get('mysql.database'),
			// Run the commands within the schema SQL file.
			file_get_contents('./database/schema.sql')
		]);
		echo("Schema created. Note that this command cannot report any errors that may have occured.\n");
	}

	public function seedDatabase($f3, $params) {
		$this->createSchema($f3, $params);
		foreach ($f3->get('seeders') as $seeder)
			$seeder::seed($f3);
		echo("Database seeded.\n");
	}

}
