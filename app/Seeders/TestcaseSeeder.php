<?php

namespace Seeders;

use \Models\Problem;
use \Models\Testcase; 

class TestcaseSeeder {

	public static function seed($f3) {
		$db = $f3->get('DB');
		$db->exec('DELETE FROM testcases');
		$db->exec('VACUUM');

		$aplusb1_1 = new Testcase();
		$aplusb1_1->problem_slug = 'aplusb';
		$aplusb1_1->subtask_number = 1;
		$aplusb1_1->input = '3 4\\n';
		$aplusb1_1->output = '7\\n';
		$aplusb1_1->save();
	}

}
