<?php

namespace Controllers;

class CommandController extends Controller {

	public function checkIfCli() {
		if (php_sapi_name() != 'cli')
			$f3->error(403);
	}

	public function seedDatabase($f3, $params) {
		$this->checkIfCli();
		foreach ($f3->get('seeders') as $seeder)
			$seeder::seed($f3);
		echo("Database seeded.\n");
	}

}
