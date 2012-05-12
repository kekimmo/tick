<?php

require_once('template.php');
require_once('refnum.php');


function entered ($order) {
	$v['quantity'] = htmlspecialchars($order->quantity);
	$v['cost'] = sprintf('%.2f', $order->cost);
	$v['name'] = htmlspecialchars($order->name);
	$v['email'] = htmlspecialchars($order->email);
	$v['street'] = htmlspecialchars($order->street);
	$v['postcode'] = htmlspecialchars($order->postcode);
	$v['postoffice'] = htmlspecialchars($order->postoffice);
	$v['refnum'] = refnum_from_id($order->id);

	$template = file_get_contents('templates/entered.html');

	return fill($template, $v);
}


?>

