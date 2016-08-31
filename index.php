<?php

use Pheanstalk\Pheanstalk;

// Kickstart F3
$f3 = require('vendor/bcosca/fatfree-core/base.php');

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION < 7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');

// Load SQLite Database
$f3->set('DB', new \DB\SQL('sqlite:database/skirmish.sqlite3'));

// Creating and seeding databases
if ($f3->get('createSchema'))
	shell_exec('sqlite3 database/skirmish.sqlite3 < database/schema.sql');
if ($f3->get('seedDatabase'))
	foreach($f3->get('seeders') as $seeder)
		$seeder::seed($f3);

// Start pheanstalk (beanstalkd client)
$pheanstalk = new Pheanstalk('127.0.0.1');
$f3->set('PHEANSTALK', $pheanstalk);

$f3->route('GET /', function($f3) {
	$f3->mset([
		'title' => 'Home',
		'content' => 'home.html'
	]);
	echo(\Template::instance()->render('layout.html'));
});

$f3->route('GET /problems', '\Controllers\ProblemController->index');
$f3->route('GET /problems/@slug', '\Controllers\ProblemController->show');
$f3->route('GET /submit', '\Controllers\SubmissionController->new');
$f3->route('POST /submit', '\Controllers\SubmissionController->create');
$f3->route('GET /login', '\Controllers\UserController->login');
$f3->route('POST /login', '\Controllers\UserController->authenticate');
$f3->route('GET /register', '\Controllers\UserController->new');
$f3->route('POST /register', '\Controllers\UserController->create');
$f3->route('GET /logout', '\Controllers\UserController->logout');

// Create a session for the user.
new \Session();

$f3->run();
