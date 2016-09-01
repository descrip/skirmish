<?php

require_once(__DIR__ . '/vendor/autoload.php');
use Pheanstalk\Pheanstalk;

// Kickstart F3
$f3 = Base::instance();

$f3->set('DEBUG',1);
if ((float)PCRE_VERSION < 7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');

// Load MySQL Database
$f3->set('DB', new \DB\SQL(
	'mysql:host=' . $f3->get('mysql.host') . ';port=' . $f3->get('mysql.port')
		.';dbname=' . $f3->get('mysql.dbname'),
	$f3->get('mysql.user'),
	$f3->get('mysql.password')
));

// Start Pheanstalk (beanstalkd client)
$f3->set('PHEANSTALK', new Pheanstalk(
	$f3->get('beanstalkd.host') . ":" . $f3->get('beanstalkd.port')
));

$f3->route('GET /', function($f3) {
	$f3->mset([
		'title' => 'Home',
		'content' => 'home.html'
	]);
	echo(\Template::instance()->render('layout.html'));
});

$f3->route('GET /seedDatabase', '\Controllers\CommandController->seedDatabase');
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
