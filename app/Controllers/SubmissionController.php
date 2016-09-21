<?php

namespace Controllers;

use \Models\Language;
use \Models\Verdict;
use \Models\User;
use \Models\Problem;
use \Models\Subtask;
use \Models\Testcase;
use \Models\Submission;
use \Models\SubtaskResult;
use \Models\TestcaseResult;
use \Controllers\ProblemController;

class SubmissionController extends Controller {

	public function new($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);
		$this->generateCsrf($f3, $params);

		$problems = (new Problem())->select(
			'id, name, slug',
			($f3->exists('SESSION.contest') ?
				['contest_id = ?', $f3->get('SESSION.contest.id')] :
				'contest_id IS NULL'
			)
		);

		$f3->mset([
			'title' => 'Submit',
			'content' => 'submissions/new.html',
			'problems' => $problems,
			'languages' => (new Language())->select('id, name, version')
		]);

		echo(\Template::instance()->render('layout.html'));
	}

	public function create($f3, $params) {
		$this->checkIfAuthenticated($f3, $params);
		if (!$this->checkCsrf($f3, $params))
			$f3->error(403);

		$problem = new Problem();
		$problem->load(['id = ?', $f3->get('POST.problemId')]);
		if ($problem->dry())
			$f3->error(404);

		$language = new Language();
		$language->load(['id = ?', $f3->get('POST.languageId')]);
		if ($language->dry())
			$f3->error(404);

		$user = new User();
		$user->load(['id = ?', $f3->get('SESSION.user.id')]);
		if ($user->dry())
			$f3->error(403);

		$submission = new Submission();
		$submission->problem_id = $problem->id;
		$submission->user_id = $user->id;
		$submission->language_id = $language->id;
		$submission->save();

		$db = $f3->get('DB');

		// Query subtasks with raw SQL so no ORM classes are used.
		$subtasks = $db->exec(
			'SELECT id FROM subtasks WHERE problem_id = ?',
			$problem->id
		);
		for ($i = 0; $i < count($subtasks); $i++)
			$subtasks[$i]['testcases'] = [];

		$subtaskIdToIndex = [];
		for ($i = 0; $i < count($subtasks); $i++)
			$subtaskIdToIndex[$subtasks[$i]['id']] = $i;

		$testcases = $db->exec(
			'SELECT testcases.* FROM subtasks
			INNER JOIN testcases
			ON subtasks.id = testcases.subtask_id
			AND subtasks.problem_id = ?',
			$problem->id
		);

		for ($i = 0; $i < count($testcases); $i++) {
			$index = $subtaskIdToIndex[intval($testcases[$i]['subtask_id'])];
			array_push($subtasks[$index]['testcases'], $testcases[$i]);
		}

		$db->begin();
		for ($i = 0; $i < count($subtasks); $i++) {
			$db->exec(
				'INSERT INTO subtask_results(submission_id, subtask_id)
				VALUES(?, ?)',
				[$submission->id, $subtasks[$i]['id']]
			);
			$subtasks[$i]['subtask_result_id'] = $db->lastInsertId();

			for ($j = 0; $j < count($subtasks[$i]['testcases']); $j++) {
				$db->exec(
					'INSERT INTO testcase_results(subtask_result_id, testcase_id)
					VALUES(?, ?)',
					[
						$subtasks[$i]['subtask_result_id'],
						$subtasks[$i]['testcases'][$j]['id']
					]
				);
			}
		}
		$db->commit();

		$f3->get('pheanstalk')->useTube('run-submission')->put(json_encode([
			'submission_id' => $submission->id,
			'compile_command' => $language->compile_command,
			'execute_command' => $language->execute_command,
			'extension' => $language->extension,
			'subtasks' => $subtasks,
			'code' => file_get_contents($f3->get('FILES.solution.tmp_name')),
			'execution_time_limit' => $problem->time_limit,
			'execution_memory_limit' => $problem->memory_limit,
			'problem_slug' => $problem->slug
		]));

		$f3->reroute('/submissions/' . $submission->id);
	}

	/* FIXME: Break a couple of HMVC rules here.
	 * Subtasks, testcases don't have their own modular show.
	 * Should be fine, I'm not planning to use them again. Hopefully.
	 */
	public function show($f3, $params) {
		$submission = new Submission();
		$submission->load(['id = ?', $params['id']]);
		if ($submission->dry())
			$f3->error(404);

		$problem = $f3->get('DB')->exec(
			'SELECT id, name, slug FROM problems WHERE id = ?',
			$submission->problem_id
		);
		if (count($problem) == 0)
			$f3->error(404);
		else if (count($problem) == 1)
			$problem = $problem[0];
		else $f3->error(500);

		$subtasks = $f3->get('DB')->exec(
			'SELECT subtasks.id, COUNT(testcases.id) AS testcase_count
			FROM subtasks
			INNER JOIN testcases
			ON subtasks.id = testcases.subtask_id
			AND subtasks.problem_id = ?
			GROUP BY subtasks.id',
			$problem['id']
		);

		$tmp = $f3->get('DB')->exec(
			'SELECT subtask_results.id, testcase_results.verdict_id
			FROM subtask_results
			INNER JOIN testcase_results
			ON subtask_results.id = testcase_results.subtask_result_id
			AND subtask_results.submission_id = ?',
			$submission->id
		);

		// Eighty character limit broken here for neatness.
		$subtask_results = [];
		for ($i = 0; $i < count($tmp); $i++) {
			$lastIndex = count($subtask_results)-1;

			if ($i == 0 || $tmp[$i]['id'] != $tmp[$i-1]['id']) {
				if ($i != 0)
					while (count($subtask_results[$lastIndex]['testcase_results']) < $subtasks[$lastIndex]['testcase_count'])
						array_push($subtask_results[$lastIndex]['testcase_results'], [
							'id' => count(subtask_results[$lastIndex]['testcase_results'])+1,
							'verdict_id' => 1
						]);

				array_push($subtask_results, [
					'id' => count($subtask_results)+1,
					'testcase_results' => []
				]);
				$lastIndex++;
			}

			array_push($subtask_results[$lastIndex]['testcase_results'], [
				'id' => count($subtask_results[$lastIndex]['testcase_results'])+1,
				'verdict_id' => $tmp[$i]['verdict_id']
			]);
		}

		$verdicts = (new Verdict())->find();

		$f3->mset([
			'title' => 'Submission #' . $params['id'],
			'problem' => $problem,
			'submission' => $submission,
			'subtask_results' => $subtask_results,
			'verdicts' => $verdicts,
			'content' => 'submissions/show.html'
		]);

		echo(\Template::instance()->render('layout.html'));
	}

}
