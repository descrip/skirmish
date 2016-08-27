-- drop all previous tables
PRAGMA writable_schema = 1;
DELETE FROM sqlite_master WHERE TYPE IN ('table', 'index', 'trigger');
PRAGMA writable_schema = 0;
VACUUM;
PRAGMA INTEGRITY_CHECK;

CREATE TABLE languages (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR UNIQUE NOT NULL,
	version VARCHAR NOT NULL,
	compile_command VARCHAR,
	execute_command VARCHAR
);

CREATE TABLE verdicts (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR NOT NULL,
	code VARCHAR NOT NULL,
	accepted INTEGER NOT NULL
);

CREATE TABLE users (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	username VARCHAR UNIQUE NOT NULL,
	password VARCHAR NOT NULL
);

CREATE TABLE problems (
	name VARCHAR UNIQUE NOT NULL,
	slug VARCHAR PRIMARY KEY NOT NULL,
	body TEXT NOT NULL
);

CREATE TABLE testcases (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	input TEXT NOT NULL,
	output TEXT NOT NULL,		-- TODO: Support for program-based checking?
	subtask_number INTEGER,		-- TODO: If NULL, no subtask grouping.
	problem_slug VARCHAR NOT NULL,
	FOREIGN KEY(problem_slug) REFERENCES problems(slug) ON UPDATE CASCADE ON DELETE CASCADE
);

CREATE TABLE submissions (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	points INTEGER NOT NULL
	problem_slug VARCHAR NOT NULL,
	user_id INTEGER NOT NULL,
	verdict_id INTEGER NOT NULL,
	language_id INTEGER NOT NULL,
	FOREIGN KEY(problem_slug) REFERENCES problems(slug) ON UPDATE CASCADE,
	FOREIGN KEY(user_id) REFERENCES users(id) ON UPDATE CASCADE,
	FOREIGN KEY(verdict_id) REFERENCES verdicts(id) ON UPDATE CASCADE,
	FOREIGN KEY(language_id) REFERENCES languages(id) ON UPDATE CASCADE
);

CREATE TABLE results (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	submission_id INTEGER NOT NULL,
	testcase_id INTEGER NOT NULL,
	verdict_id INTEGER NOT NULL,
	FOREIGN KEY(submission_id) REFERENCES submissions(id) ON UPDATE CASCADE,
	FOREIGN KEY(testcase_id) REFERENCES testcases(id) ON UPDATE CASCADE,
	FOREIGN KEY(verdict_id) REFERENCES verdicts(id) ON UPDATE CASCADE
);