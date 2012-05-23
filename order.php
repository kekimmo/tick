<?php

require_once('order_config.php');


class Order {
	var $id;
	var $quantity;
	var $cost;
	var $name;
	var $email;
	var $street;
	var $postcode;
	var $postoffice;
	var $entered;
	var $paid;
	var $mailed;


	static function cost ($quantity) {
		return $quantity * Config::TICKET_PRICE + Config::POSTAGE;
	}

	function due () {
		return Config::due($this->entered);
	}
};


function order_data ($order) {
	$refnum = refnum_from_id($order->id);
	$due = $order->due();
	return array(
		'refnum' => $refnum,
		'quantity' => $order->quantity,
		'cost' => Config::money_fmt($order->cost),
		'postage' => Config::money_fmt(Config::POSTAGE),
		'name' => $order->name,
		'email' => $order->email,
		'street' => $order->street,
		'postcode' => $order->postcode,
		'postoffice' => $order->postoffice,
		'account' => Config::ACCOUNT_NUMBER,
		'due' => date('j.n.Y', $due),
		'barcode' => barcode($order->cost, $refnum, $due),
		'paid' => ($order->paid !== null ? date('j.n.Y', $order->paid) : '<?>'),
		'mailed' => ($order->mailed !== null ? date('j.n.Y', $order->mailed) : '<?>'),
	);
}


?>
