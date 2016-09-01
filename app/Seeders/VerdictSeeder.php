<?php

namespace Seeders;

use \Models\Verdict;

class VerdictSeeder {

	public static function seed($f3) {
		$db = $f3->get('DB');
		$db->exec('DELETE FROM verdicts');

		$qu = new Verdict();
		$qu->name = 'In Queue';
		$qu->code = 'QU';
		$qu->save();

		$ce = new Verdict();
		$ce->name = 'Compile Error';
		$ce->code = 'CE';
		$ce->save();

		$re = new Verdict();
		$re->name = 'Runtime Error';
		$re->code = 'RE';
		$re->save();

		$tle = new Verdict();
		$tle->name = 'Time Limit Exceeded';
		$tle->code = 'TLE';
		$tle->save();

		$mle = new Verdict();
		$mle->name = 'Memory Limit Exceeded';
		$mle->code = 'MLE';
		$mle->save();

		$wa = new Verdict();
		$wa->name = 'Wrong Answer';
		$wa->code = 'WA';
		$wa->save();

		$ac = new Verdict();
		$ac->name = 'Accepted';
		$ac->code = 'AC';
		$ac->is_accepted = 1;
		$ac->save();
	}

}
