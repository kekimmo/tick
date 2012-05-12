<?php


require_once('db.php');
require_once('queries.php');


header('Content-Type: text/plain');

$dbh = db_connect();
$stmt = $dbh->query('SELECT SUM(quantity) AS quantity, SUM(cost) AS cost FROM orders');
$res = $stmt->fetch(PDO::FETCH_ASSOC);
$quantity = (int)$res['quantity'];
$cost = (float)$res['cost'];


printf("Lippuja myyty: %d / %d\n", $quantity, Config::TICKETS_TOTAL);
printf("Rahaa saatu: %.2f €\n", $cost);

?>

