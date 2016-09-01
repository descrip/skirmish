<?php

namespace Seeders;

use \Models\Verdict;

class VerdictSeeder {

	public static function seed($f3) {
		$db = $f3->get('DB');
		$db->exec('DELETE FROM verdicts');

		$wa = new Verdict();
		$wa->name = "Wrong Answer";
		$wa->code = "WA";
		$wa->accepted = 0;

		$ac = new Verdict();
		$ac->name = "Accepted";
		$ac->code = "AC";
		$ac->accepted = 1;
	}

}
