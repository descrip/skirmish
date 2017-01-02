<?php

require_once('./vendor/autoload.php');
use Pheanstalk\Pheanstalk;

// Kickstart F3
$f3 = Base::instance();

if ((float)PCRE_VERSION < 7.9)
	trigger_error('PCRE version is out of date');

// Load configuration
$f3->config('config.ini');

// Load MySQL Database
$f3->set('DB', new \DB\SQL(
	'mysql:host=' . $f3->get('mysql.host') . ';port=' . $f3->get('mysql.port')
		. ';dbname=' . $f3->get('mysql.database'),
	$f3->get('mysql.user'),
	$f3->get('mysql.password')
));

new \DB\SQL\Session($f3->get('DB'));

// Start Pheanstalk (beanstalkd client)
$f3->set('pheanstalk', new Pheanstalk($f3->get('beanstalkd.host')));

$f3->route('GET /', function($f3) {
	$f3->mset([
		'title' => 'Home',
		'content' => $f3->get('THEME') . '/views/home.html'
	]);
	echo(\Template::instance()->render($f3->get('THEME') . '/views/layout.html'));
});

//$f3->route('GET create-schema', '\Controllers\CommandController->createSchema');
$f3->route('GET /seed-database', '\Controllers\CommandController->seedDatabase');
$f3->route('GET /problems', '\Controllers\ProblemController->index');
$f3->route('GET /problems/@slug', '\Controllers\ProblemController->show');
$f3->route('GET /submit', '\Controllers\SubmissionController->new');
$f3->route('POST /submit', '\Controllers\SubmissionController->create');
$f3->route('GET /leaderboard', '\Controllers\UserController->index');
$f3->route('GET /login', '\Controllers\UserController->login');
$f3->route('POST /login', '\Controllers\UserController->authenticate');
$f3->route('GET /register', '\Controllers\UserController->register');
$f3->route('POST /register', '\Controllers\UserController->create');
$f3->route('GET /logout', '\Controllers\UserController->logout');
$f3->route('GET /submissions/@id', '\Controllers\SubmissionController->show');
$f3->route('GET /contests', '\Controllers\ContestController->index');
$f3->route('GET /contests/@slug', '\Controllers\ContestController->show');
$f3->route('GET /contests/@slug/enter', '\Controllers\ContestController->enter');
$f3->route('GET /contests/@slug/leave', '\Controllers\ContestController->leave');

$f3->run();
