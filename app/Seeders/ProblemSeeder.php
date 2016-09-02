<?php

namespace Seeders;

use \Models\Problem;
use \Models\Testcase;
use \Models\Subtask;

class ProblemSeeder {

	public static function seed($f3) {
		$db = $f3->get('DB');
		$db->exec('DELETE FROM problems');

		$aplusb = new Problem();
		$aplusb->name = 'A Plus B';
		$aplusb->slug = 'aplusb';
		$aplusb->body = <<<MARKER
# A Plus B
Given two integers \$A\$ and \$B\$, where \$(1 \\leq A, B \\leq 100)\$, print the sum of \$A+B\$.
### Input Specification
On the first and only line will be the two integers, \$A\$ and \$B\$, separated by a space.
### Output Specification
Print a single line with the value of \$A+B\$.
### Sample Input
`3 4`
### Sample Output
`7`
MARKER;
		$aplusb->save();

		$aplusb1 = new Subtask();
		$aplusb1->problem_id = $aplusb->id;
		$aplusb1->save();

			$aplusb1_1 = new Testcase();
			$aplusb1_1->input = "3 4\n";
			$aplusb1_1->output = "7\n";
			$aplusb1_1->subtask_id = $aplusb1->id;
			$aplusb1_1->save();

		$atimesb = new Problem();
		$atimesb->name = 'A Times B';
		$atimesb->slug = 'atimesb';
		$atimesb->body = <<<MARKER
# A Times B
quack
MARKER;
		$atimesb->save();
	}

}
