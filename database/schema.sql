CREATE TABLE languages (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) UNIQUE NOT NULL,
	version VARCHAR(255) NOT NULL,
	extension VARCHAR(255) NOT NULL,
	compile_command VARCHAR(255),
	execute_command VARCHAR(255) NOT NULL
);

CREATE TABLE verdicts (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) NOT NULL,
	code VARCHAR(255) UNIQUE NOT NULL,
	is_accepted BOOLEAN NOT NULL DEFAULT 0,
    priority INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE contests (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) UNIQUE NOT NULL,
	slug VARCHAR(255) UNIQUE NOT NULL,
	body TEXT NOT NULL,
	start_time TIMESTAMP NOT NULL DEFAULT '1970-01-01 00:00:01',
	end_time TIMESTAMP NOT NULL DEFAULT '1970-01-01 00:00:01'
);

CREATE TABLE users (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	username VARCHAR(255) UNIQUE NOT NULL,
	email VARCHAR(255) UNIQUE NOT NULL,
	password VARCHAR(255) NOT NULL,
	points INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE problems (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	name VARCHAR(255) UNIQUE NOT NULL,
	slug VARCHAR(255) UNIQUE NOT NULL,
	body TEXT NOT NULL,
	memory_limit INTEGER NOT NULL,
	time_limit INTEGER NOT NULL,
	points INTEGER NOT NULL,
	marks INTEGER NOT NULL DEFAULT 0,
	contest_id INTEGER,
	FOREIGN KEY(contest_id) REFERENCES contests(id) ON DELETE CASCADE
);

CREATE TABLE subtasks (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	problem_id INTEGER NOT NULL,
	marks INTEGER NOT NULL DEFAULT 0,
	FOREIGN KEY(problem_id) REFERENCES problems(id) ON DELETE CASCADE
);

CREATE TABLE testcases (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	input TEXT NOT NULL,
	output TEXT NOT NULL,	-- TODO: Support for program-based checking?
	subtask_id INTEGER NOT NULL,
	marks INTEGER NOT NULL,
	FOREIGN KEY(subtask_id) REFERENCES subtasks(id) ON DELETE CASCADE
);

CREATE TABLE submissions (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	problem_id INTEGER NOT NULL,
	user_id INTEGER NOT NULL,
	verdict_id INTEGER NOT NULL DEFAULT 1,
	language_id INTEGER NOT NULL,
	marks INTEGER NOT NULL DEFAULT 0,
	points INTEGER NOT NULL DEFAULT 0,
	FOREIGN KEY(problem_id) REFERENCES problems(id),
	FOREIGN KEY(user_id) REFERENCES users(id),
	FOREIGN KEY(verdict_id) REFERENCES verdicts(id),
	FOREIGN KEY(language_id) REFERENCES languages(id)
);

CREATE TABLE subtask_results (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	submission_id INTEGER NOT NULL,
	subtask_id INTEGER NOT NULL,
	marks INTEGER NOT NULL DEFAULT 0,
	verdict_id INTEGER NOT NULL DEFAULT 1,
	FOREIGN KEY(verdict_id) REFERENCES verdicts(id),
	FOREIGN KEY(submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
	FOREIGN KEY(subtask_id) REFERENCES subtasks(id)
);

CREATE TABLE testcase_results (
	id INTEGER PRIMARY KEY AUTO_INCREMENT,
	subtask_result_id INTEGER NOT NULL,
	testcase_id INTEGER NOT NULL,
	verdict_id INTEGER NOT NULL DEFAULT 1,
	FOREIGN KEY(subtask_result_id) REFERENCES subtask_results(id) ON DELETE CASCADE,
	FOREIGN KEY(testcase_id) REFERENCES testcases(id),
	FOREIGN KEY(verdict_id) REFERENCES verdicts(id)
);

CREATE TABLE users_solved_problems_pivot (
	user_id INTEGER NOT NULL,
	FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
	problem_id INTEGER NOT NULL,
	FOREIGN KEY(problem_id) REFERENCES problems(id) ON DELETE CASCADE,
	submission_id INTEGER NOT NULL,
	FOREIGN KEY(submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
	PRIMARY KEY(user_id, problem_id)
);

CREATE TABLE users_entered_contests_pivot (
	user_id INTEGER NOT NULL,
	FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
	contest_id INTEGER NOT NULL,
	FOREIGN KEY(contest_id) REFERENCES contests(id) ON DELETE CASCADE,
	PRIMARY KEY(user_id, contest_id)
);

DELIMITER //

CREATE PROCEDURE update_subtask_result_status (IN subtask_result_id INTEGER)
    UPDATE subtask_results SET
        marks = IFNULL((
            SELECT SUM(testcases.marks) FROM testcase_results 
            LEFT JOIN testcases ON testcase_results.testcase_id = testcases.id 
            LEFT JOIN verdicts ON testcase_results.verdict_id = verdicts.id
            WHERE testcase_results.subtask_result_id = subtask_result_id
            AND verdicts.is_accepted
        ), 0),
        verdict_id = IFNULL((
            SELECT verdicts.id FROM verdicts
            RIGHT JOIN testcase_results
            ON testcase_results.verdict_id = verdicts.id
            WHERE testcase_results.subtask_result_id = subtask_result_id
            ORDER BY verdicts.priority ASC
            LIMIT 1
        ), 1)
    WHERE id = subtask_result_id//

CREATE PROCEDURE update_submission_status (IN submission_id INTEGER)
    UPDATE submissions SET
        marks = IFNULL((
            SELECT SUM(marks) FROM subtask_results 
            WHERE submission_id = submission_id
        ), 0),
        verdict_id = IFNULL((
            SELECT verdicts.id FROM verdicts
            RIGHT JOIN subtask_results
            ON subtask_results.verdict_id = verdicts.id
            WHERE subtask_results.submission_id = submission_id
            ORDER BY verdicts.priority ASC
            LIMIT 1
        ), 1)
    WHERE id = submission_id//

CREATE TRIGGER update_subtask_result AFTER UPDATE
ON testcase_results FOR EACH ROW
    BEGIN
        IF NEW.subtask_result_id != OLD.subtask_result_id THEN
            CALL update_subtask_result_status(OLD.subtask_result_id);
        END IF;
        CALL update_subtask_result_status(NEW.subtask_result_id);
    END//

CREATE TRIGGER update_submission AFTER UPDATE
ON subtask_results FOR EACH ROW
    BEGIN
        IF NEW.submission_id != OLD.submission_id THEN
            CALL update_submission_status(OLD.submission_id);
        END IF;
        CALL update_submission_status(NEW.submission_id);
    END//

DELIMITER ;
