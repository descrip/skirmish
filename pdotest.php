<?php

$db = new PDO('mysql:host=localhost;dbname=skirmishdb;port=3306;charset=utf8', 'root', 'secret');


$stmt = $db->prepare('SELECT * FROM problems WHERE id = :id');
$stmt->bindParam(':id', $id);
$stmt->bindParam(':cool', $cool);

$id = 1;

$stmt->execute();
var_dump($stmt->fetchAll());
