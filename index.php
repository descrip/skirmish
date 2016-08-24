<?php

// Kickstart the framework
$f3 = require('lib/base.php');

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION < 7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
// TODO: Move everything to config.ini
$f3->config('config.ini');

// Load Jig NoSQL
$f3->set('DB', new DB\Jig('app/database/'));

// Autoload important classes when they're needed
$f3->set('AUTOLOAD', 'app/controllers/');

$f3->route('GET /', function($f3) {
	$f3->set('title', 'Home');
	$f3->set('content', 'app/views/home.htm');
	echo \Template::instance()->render('app/views/layout.htm');
});

$f3->run();
