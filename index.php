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

//var_dump(\Util\Validate::isValidEmail(''));

//$f3->route('GET create-schema', '\Controllers\CommandController->createSchema');
$f3->route('GET seed-database', '\Controllers\CommandController->seedDatabase');

$f3->route('GET @home: /', '\Controllers\HomeController->home');

$f3->route('GET @problemsIndex: /problems', '\Controllers\ProblemController->index');
$f3->route('GET @problemsShow: /problems/@slug', '\Controllers\ProblemController->show');
$f3->route('GET @problemsSubmit: /problems/@slug/submit', '\Controllers\SubmissionController->submit');
$f3->route('GET @problemsAllSubmissions: /problems/@slug/all-submissions', '\Controllers\ProblemController->allSubmissions');
$f3->route('GET @problemsBestSubmissions: /problems/@slug/best-submissions', '\Controllers\ProblemController->bestSubmissions');
$f3->route('GET @problemsYourSubmissions: /problems/@slug/your-submissions', '\Controllers\ProblemController->yourSubmissions');

$f3->route('GET @submissionsSubmit: /submit', '\Controllers\SubmissionController->submit');
$f3->route('POST @submissionsSubmit: /submit', '\Controllers\SubmissionController->create');
$f3->route('GET @submissionsIndex: /submissions', '\Controllers\SubmissionController->index');
$f3->route('GET @submissionsShow: /submissions/@id', '\Controllers\SubmissionController->show');

$f3->route('GET @usersLeaderboard: /leaderboard', '\Controllers\UserController->leaderboard');
$f3->route('GET @usersLogin: /login', '\Controllers\UserController->login');
$f3->route('POST @usersLogin: /login', '\Controllers\UserController->authenticate');
$f3->route('GET @usersRegister: /register', '\Controllers\UserController->register');
$f3->route('POST @usersRegister: /register', '\Controllers\UserController->create');
$f3->route('GET @usersLogout: /logout', '\Controllers\UserController->logout');
$f3->route('GET @usersShow: /users/@username', '\Controllers\UserController->show');

/*
$f3->route('GET /contests', '\Controllers\ContestController->index');
$f3->route('GET /contests/@slug', '\Controllers\ContestController->show');
$f3->route('GET /contests/@slug/enter', '\Controllers\ContestController->enter');
$f3->route('GET /contests/@slug/leave', '\Controllers\ContestController->leave');
 */

$f3->run();
