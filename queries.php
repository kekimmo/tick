<?php

require_once('config.php');
require_once('db.php');
require_once('order.php');


function tickets_total ($dbh) {
	return Config::TICKETS_TOTAL;
}


function tickets_available ($dbh) {
	return Config::TICKETS_TOTAL - tickets_ordered($dbh);
}


function tickets_ordered ($dbh) {
	$stmt = $dbh->query('SELECT SUM(quantity) FROM orders');
	return $stmt->fetchColumn();
}


function order_enter ($dbh, $quantity, $name, $email, $street, $postcode, $postoffice) {
	$sql = 'INSERT INTO orders SET
		quantity = :quantity,
		cost = :cost,
		name = :name,
		email = :email,
		street = :street,
		postcode = :postcode,
		postoffice = :postoffice';
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(
		':quantity' => $quantity,
		':cost' => $quantity * Config::TICKET_PRICE,
		':name' => $name,
		':email' => $email,
		':street' => $street,
		':postcode' => $postcode,
		':postoffice' => $postoffice));
	return intval($dbh->lastInsertId());
}


function order_by_id ($dbh, $id) {
	$sql = 'SELECT
			id,
			quantity,
			cost,
			name,
			email,
			street,
			postcode,
			postoffice,
			UNIX_TIMESTAMP(entered) AS entered,
			UNIX_TIMESTAMP(paid) AS paid,
			UNIX_TIMESTAMP(mailed) AS mailed
		FROM orders WHERE id = :id';
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(':id' => $id));
	$o = $stmt->fetchObject();

	$order = new Order();
	$order->id = intval($o->id);
	$order->quantity = intval($o->quantity);
	$order->cost = floatval($o->cost);
	$order->name = $o->name;
	$order->email = $o->email;
	$order->street = $o->street;
	$order->postcode = $o->postcode;
	$order->postoffice = $o->postoffice;
	$order->entered = intval($o->entered);
	$order->paid = ($o->paid !== null ? intval($o->paid) : null);
	$order->mailed = ($o->paid !== null ? intval($o->mailed) : null);

	return $order;
}





?>
