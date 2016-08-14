<?php
$f3 = require('./lib/base.php');
$f3->route('GET /', function() {
	$view = new View;
	echo $view->render('gui/layout.html');
});
$f3->run();
