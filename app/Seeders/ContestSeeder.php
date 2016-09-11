<?php

namespace Seeders;

use \Models\Contest;
use \Models\Problem;
use \Models\Testcase;
use \Models\Subtask;

use Util;

class ContestSeeder {

	public static function seed($f3) {
		$db = $f3->get('DB');
		$db->exec('DELETE FROM contests');

		$practice = new Contest();
		$practice->slug = 'practice';
		$practice->name = 'Practice Contest';
		$practice->body = <<<MARKER
# Practice Contest

A contest prepared by Jeffrey Zhao to train for the CCC.
MARKER;
		$practice->start_time = Util::getMySqlTimestamp(time());
		// Contest duration of one hour from current time.
		$practice->end_time = Util::getMySqlTimestamp(time() + 60 * 60);
		$practice->save();

			$prac = new Problem();
			$prac->name = 'Practice Problem 1';
			$prac->slug = 'prac';
			$prac->body = <<<MARKER
# Practice Problem 1

Here's a practice problem I guess.
MARKER;
			$prac->time_limit = 3;
			$prac->memory_limit = 512000;
			$prac->points = 7;
			$prac->contest_id = $practice->id;
			$prac->save();
			
				$prac1 = new Subtask();
				$prac1->problem_id = $prac->id;
				$prac1->save();

					$prac1_1 = new Testcase();
					$prac1_1->input = "3 4\n";
					$prac1_1->output = "7\n";
					$prac1_1->subtask_id = $prac1->id;
					$prac1_1->marks = 1;
					$prac1_1->save();

					$prac1_2 = new Testcase();
					$prac1_2->input = "1 1\n";
					$prac1_2->output = "2\n";
					$prac1_2->subtask_id = $prac1->id;
					$prac1_2->marks = 2;
					$prac1_2->save();

					$prac1_3 = new Testcase();
					$prac1_3->input = "19 32\n";
					$prac1_3->output = "51\n";
					$prac1_3->subtask_id = $prac1->id;
					$prac1_3->marks = 3;
					$prac1_3->save();

				$prac2 = new Subtask();
				$prac2->problem_id = $prac->id;
				$prac2->save();

					$prac2_1 = new Testcase();
					$prac2_1->input = "6 99\n";
					$prac2_1->output = "105\n";
					$prac2_1->subtask_id = $prac2->id;
					$prac2_1->marks = 3;
					$prac2_1->save();

					$prac2_2 = new Testcase();
					$prac2_2->input = "100 100\n";
					$prac2_2->output = "200\n";
					$prac2_2->subtask_id = $prac2->id;
					$prac2_2->marks = 1;
					$prac2_2->save();
	}

}
