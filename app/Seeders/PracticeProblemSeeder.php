<?php

namespace Seeders;

use \Models\Problem;
use \Models\Testcase;
use \Models\Subtask;

class PracticeProblemSeeder {

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
		$aplusb->time_limit = 1;
		$aplusb->memory_limit = 64000;
		$aplusb->points = 2;
		$aplusb->save();

		$aplusb1 = new Subtask();
		$aplusb1->problem_id = $aplusb->id;
		$aplusb1->save();

			$aplusb1_1 = new Testcase();
			$aplusb1_1->input = "3 4\n";
			$aplusb1_1->output = "7\n";
			$aplusb1_1->subtask_id = $aplusb1->id;
			$aplusb1_1->marks = 1;
			$aplusb1_1->save();

			$aplusb1_2 = new Testcase();
			$aplusb1_2->input = "1 1\n";
			$aplusb1_2->output = "2\n";
			$aplusb1_2->subtask_id = $aplusb1->id;
			$aplusb1_2->marks = 2;
			$aplusb1_2->save();

			$aplusb1_3 = new Testcase();
			$aplusb1_3->input = "19 32\n";
			$aplusb1_3->output = "51\n";
			$aplusb1_3->subtask_id = $aplusb1->id;
			$aplusb1_3->marks = 3;
			$aplusb1_3->save();

		$aplusb2 = new Subtask();
		$aplusb2->problem_id = $aplusb->id;
		$aplusb2->save();

			$aplusb2_1 = new Testcase();
			$aplusb2_1->input = "6 99\n";
			$aplusb2_1->output = "105\n";
			$aplusb2_1->subtask_id = $aplusb2->id;
			$aplusb2_1->marks = 3;
			$aplusb2_1->save();

			$aplusb2_2 = new Testcase();
			$aplusb2_2->input = "100 100\n";
			$aplusb2_2->output = "200\n";
			$aplusb2_2->subtask_id = $aplusb2->id;
			$aplusb2_2->marks = 1;
			$aplusb2_2->save();

		/*
		$atimesb = new Problem();
		$atimesb->name = 'A Times B';
		$atimesb->slug = 'atimesb';
		$atimesb->body = <<<MARKER
# A Times B
quack
MARKER;
		$atimesb->save();
		 */
	}

}
