<?php


require_once('order_config.php');


function db_connect () {
	$s = sprintf('mysql:host=%s;dbname=%s', Config::DB_HOST, Config::DB_NAME);
	$dbh = new PDO($s, Config::DB_USER, Config::DB_PASS);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}


?>
