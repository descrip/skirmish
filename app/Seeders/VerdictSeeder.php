<?php

namespace Seeders;

use \Models\Verdict;

class VerdictSeeder {

	public static function seed($f3) {
		$db = $f3->get('DB');
		$db->exec('DELETE FROM verdicts');

		// 1
		$qu = new Verdict();
		$qu->name = 'In Queue';
		$qu->code = 'QU';
		$qu->save();

		// 2
		$ce = new Verdict();
		$ce->name = 'Compile Error';
		$ce->code = 'CE';
		$ce->save();

		// 3
		$re = new Verdict();
		$re->name = 'Runtime Error';
		$re->code = 'RE';
		$re->save();

		// 4
		$tle = new Verdict();
		$tle->name = 'Time Limit Exceeded';
		$tle->code = 'TLE';
		$tle->save();

		// 5
		$wa = new Verdict();
		$wa->name = 'Wrong Answer';
		$wa->code = 'WA';
		$wa->save();

		// 6
		$ac = new Verdict();
		$ac->name = 'Accepted';
		$ac->code = 'AC';
		$ac->is_accepted = true;
		$ac->save();
	}

}
