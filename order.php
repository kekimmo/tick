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


?>
