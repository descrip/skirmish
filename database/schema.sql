-- Create all of the tables.
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
	is_accepted BOOLEAN NOT NULL DEFAULT 0
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

-- Create triggers on testcases to update mark on subtasks on any C(R)UD.
CREATE TRIGGER update_marks_on_subtasks_on_insert BEFORE INSERT ON testcases FOR EACH ROW
UPDATE subtasks SET marks = marks + NEW.marks WHERE id = NEW.subtask_id;

CREATE TRIGGER update_marks_on_subtasks_on_update BEFORE UPDATE ON testcases FOR EACH ROW
BEGIN
	UPDATE subtasks SET marks = marks - OLD.marks WHERE id = OLD.subtask_id;
	UPDATE subtasks SET marks = marks + NEW.marks WHERE id = NEW.subtask_id;
END;

CREATE TRIGGER update_marks_on_subtasks_on_delete BEFORE DELETE ON testcases FOR EACH ROW
UPDATE subtasks SET marks = marks - OLD.marks WHERE id = OLD.subtask_id;

-- Create triggers on subtasks to relay that mark information to problems on C(R)UD.
CREATE TRIGGER update_marks_on_problems_on_insert BEFORE INSERT ON subtasks FOR EACH ROW
UPDATE problems SET marks = marks + NEW.marks WHERE id = NEW.problem_id;

CREATE TRIGGER update_marks_on_problems_on_update BEFORE UPDATE ON subtasks FOR EACH ROW
BEGIN
	UPDATE problems SET marks = marks - OLD.marks WHERE id = OLD.problem_id;
	UPDATE problems SET marks = marks + NEW.marks WHERE id = NEW.problem_id;
END;

CREATE TRIGGER update_marks_on_problems_on_delete BEFORE DELETE ON subtasks FOR EACH ROW
UPDATE problems SET marks = marks - OLD.marks WHERE id = OLD.problem_id;

-- Create triggers on testcase_results to update mark on subtask_results on any C(R)UD if an accepted verdict was assigned.
CREATE TRIGGER update_marks_on_subtask_results_on_insert BEFORE INSERT ON testcase_results FOR EACH ROW
BEGIN
	SELECT is_accepted INTO @is_accepted FROM verdicts WHERE id = NEW.verdict_id;
	SELECT marks INTO @marks FROM testcases WHERE id = NEW.testcase_id;
	IF @is_accepted THEN
		UPDATE subtask_results SET marks = marks + @marks WHERE id = NEW.subtask_result_id;
	END IF;
END;

CREATE TRIGGER update_marks_on_subtask_results_on_update BEFORE UPDATE ON testcase_results FOR EACH ROW
BEGIN
	SELECT is_accepted INTO @old_is_accepted FROM verdicts WHERE id = OLD.verdict_id;
	SELECT marks INTO @old_marks FROM testcases WHERE id = OLD.testcase_id;
	SELECT is_accepted INTO @new_is_accepted FROM verdicts WHERE id = NEW.verdict_id;
	SELECT marks INTO @new_marks FROM testcases WHERE id = NEW.testcase_id;
	IF @old_is_accepted THEN
		UPDATE subtask_results SET marks = marks - @old_marks WHERE id = OLD.subtask_result_id;
	END IF;
	IF @new_is_accepted THEN
		UPDATE subtask_results SET marks = marks + @new_marks WHERE id = NEW.subtask_result_id;
	END IF;
END;

CREATE TRIGGER update_marks_on_subtask_results_on_delete BEFORE DELETE ON testcase_results FOR EACH ROW
BEGIN
	SELECT is_accepted INTO @is_accepted FROM verdicts WHERE id = OLD.verdict_id;
	SELECT marks INTO @marks FROM testcases WHERE id = OLD.testcase_id;
	IF @is_accepted THEN
		UPDATE subtask_results SET marks = marks - @marks WHERE id = OLD.subtask_result_id;
	END IF;
END;

-- Create triggers on subtask_results to relay information to submissions on any C(R)UD.
CREATE TRIGGER update_marks_on_submissions_on_insert BEFORE INSERT ON subtask_results FOR EACH ROW
UPDATE submissions SET marks = marks + NEW.marks WHERE id = NEW.submission_id;

CREATE TRIGGER update_marks_on_submissions_on_update BEFORE UPDATE ON subtask_results FOR EACH ROW
BEGIN
	UPDATE submissions SET marks = marks - OLD.marks WHERE id = OLD.submission_id;
	UPDATE submissions SET marks = marks + NEW.marks WHERE id = NEW.submission_id;
END;

CREATE TRIGGER update_marks_on_submissions_on_delete BEFORE DELETE ON subtask_results FOR EACH ROW
UPDATE submissions SET marks = marks - OLD.marks WHERE id = OLD.submission_id;
