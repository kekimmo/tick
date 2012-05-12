<?php


require_once('template.php');


function mail_order_confirmation ($v) {
	$v = array(
		'refnum' => refnum_from_id($order->id),
		'quantity' => $order->quantity,
		'cost' => sprintf('%.2f', $order->cost),
		'name' => $order->name,
		'email' => $order->email,
		'street' => $order->street,
		'postcode' => $order->postcode,
		'postoffice' => $order->postoffice
	);

	$template = file_get_contents('templates/mail/order_confirmation.txt');
	print fill($template, $v);

	return true;
}


?>
