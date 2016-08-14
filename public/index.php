<?php
$f3 = require('../vendor/bcosca/fatfree/lib/base.php');
$f3->route('GET /', function() {
	$view = new View;
	echo $view->render('index.htm');
});
$f3->run();
