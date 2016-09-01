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

		$wa = new Verdict();
		$wa->name = 'Wrong Answer';
		$wa->code = 'WA';
		$wa->save();

		$ac = new Verdict();
		$ac->name = 'Accepted';
		$ac->code = 'AC';
		$ac->accepted = 1;
		$ac->save();
	}

}
