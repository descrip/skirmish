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
	}

}
