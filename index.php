<?php

// Kickstart the framework
$f3 = require('lib/base.php');

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION < 7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
// TODO: Move everything to config.ini
$f3->config('config.ini');

$f3->set('CACHE', true);
$f3->set('AUTOLOAD', 'app/');
// Load Jig NoSQL
$f3->set('DB', new DB\Jig('app/database/'));

$f3->route('GET /', function($f3) {
	$f3->set('title', 'Home');
	$f3->set('content', 'app/views/home.htm');
	echo(\Template::instance()->render('app/views/layout.htm'));
});

$user = new \Models\User();

$f3->route('GET /problems/@problem', '\Controllers\ProblemController::show');

$f3->run();
