<?php

require_once('order_config.php');
require_once('db.php');
require_once('order.php');


class QueryException extends Exception {};


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


function order_already_entered ($dbh, $confirm_id) {
	$sql = 'SELECT id FROM orders WHERE confirm_id = :confirm_id';
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array('confirm_id' => $confirm_id));
	$id = $stmt->fetchColumn();
	if ($id !== false) {
		return intval($id);
	}
	else {
		return null;
	}
}


function order_enter ($dbh, $quantity, $name, $email, $street, $postcode, $postoffice, $confirm_id) {
	$sql = 'INSERT INTO orders SET
		quantity = :quantity,
		cost = :cost,
		name = :name,
		email = :email,
		street = :street,
		postcode = :postcode,
		postoffice = :postoffice,
		confirm_id = :confirm_id';
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(
		':quantity' => $quantity,
		':cost' => Order::cost($quantity),
		':name' => $name,
		':email' => $email,
		':street' => $street,
		':postcode' => $postcode,
		':postoffice' => $postoffice,
		':confirm_id' => $confirm_id
	));
	return intval($dbh->lastInsertId());
}


define('ORDER_FIELDS', 'id,
						quantity,
						cost,
						name,
						email,
						street,
						postcode,
						postoffice,
						UNIX_TIMESTAMP(entered) AS entered,
						UNIX_TIMESTAMP(paid) AS paid,
						UNIX_TIMESTAMP(mailed) AS mailed');


function order_from_o ($o) {
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
	$order->mailed = ($o->mailed !== null ? intval($o->mailed) : null);

	return $order;	
}


function order_by_id ($dbh, $id) {
	$sql = 'SELECT ' . ORDER_FIELDS . ' FROM orders WHERE id = :id';
	$stmt = $dbh->prepare($sql);
	$stmt->execute(array(':id' => $id));
	$o = $stmt->fetchObject();

	return order_from_o($o);
}


function orders ($dbh) {
	$sql = 'SELECT ' . ORDER_FIELDS .
		' FROM orders ORDER BY entered DESC';
	$stmt = $dbh->query($sql);
	$orders = array();
	while (($o = $stmt->fetchObject()) !== false) {
		$orders[] = order_from_o($o);
	}

	return $orders;
}


function order_set_event ($dbh, $id, $event, $state) {
	if (!in_array($event, array('paid', 'mailed'))) {
		throw new QueryException(sprintf('Invalid event: %s', $event));
	}
	$sql = sprintf('UPDATE orders SET %s = %s WHERE id = :id',
		$event, ($state ? 'NOW()' : 'NULL'));
	$stmt = $dbh->prepare($sql);
	return $stmt->execute(array(':id' => $id));
}


?>
