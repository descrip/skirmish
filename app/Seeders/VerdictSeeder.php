<?php

namespace Seeders;

use \Models\Verdict;

class VerdictSeeder {

	public static function seed($f3) {
        // TODO: Document:
        //  - Default verdict is id 1
        //  - Must have default verdict or bad stuff happens in SQL

		$db = $f3->get('DB');
		$db->exec('DELETE FROM verdicts');

		// 1
		$qu = new Verdict();
		$qu->name = 'In Queue';
		$qu->code = 'QU';
        $qu->priority = 100;
		$qu->save();

		// 2
		$ac = new Verdict();
		$ac->name = 'Accepted';
		$ac->code = 'AC';
		$ac->is_accepted = true;
        $ac->priority = 600;
		$ac->save();

		// 3
		$wa = new Verdict();
		$wa->name = 'Wrong Answer';
		$wa->code = 'WA';
        $wa->priority = 200;
		$wa->save();

		// 4
		$tle = new Verdict();
		$tle->name = 'Time Limit Exceeded';
		$tle->code = 'TLE';
        $tle->priority = 300;
		$tle->save();

		// 5
		$re = new Verdict();
		$re->name = 'Runtime Error';
		$re->code = 'RE';
        $re->priority = 400;
		$re->save();

		// 6
		$ce = new Verdict();
		$ce->name = 'Compile Error';
		$ce->code = 'CE';
        $ce->priority = 500;
		$ce->save();
	}

}
