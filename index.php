<?php

// Kickstart the framework
$f3 = require('lib/base.php');

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION < 7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');

// Load SQLite Database
$f3->set('DB', new DB\SQL('sqlite:database/skirmish.sqlite3'));

if ($f3->get('CREATESCHEMA'))
	shell_exec('sqlite3 database/skirmish.sqlite3 < database/schema.sql');
if ($f3->get('SEEDDATABASE'))
	foreach($f3->get('SEEDERS') as $seeder)
		$seeder::seed($f3);

$f3->route('GET /', function($f3) {
	$f3->set('title', 'Home');
	$f3->set('content', 'home.html');
	echo(\Template::instance()->render('layout.html'));
});

$f3->route('GET /problems/@slug', '\Controllers\ProblemController::show');
$f3->route('GET /problems', '\Controllers\ProblemController::list');
$f3->route('GET /submit', '\Controllers\SubmissionController::create');
$f3->route('POST /submit', '\Controllers\SubmissionController::store');
$f3->route('GET /login', '\Controllers\SubmissionController::store');

$f3->run();
