<?php


require_once('db.php');
require_once('refnum.php');
require_once('queries.php');
require_once('validate.php');
require_once('template.php');
require_once('forms.php');


function form () {
	try {
		$dbh = db_connect();

		$dbh->exec('LOCK TABLES orders WRITE');

		$tickets_available = tickets_available($dbh);

		if ($tickets_available < 1) {
			return fill_file('templates/sold_out.html');
		}

		$ordered = array_key_exists('order', $_POST);
		$confirmed = array_key_exists('confirm', $_POST);

		if ($ordered || $confirmed) {
			$errors = validate($dbh, $_POST);
			if ($errors) {
				return order_form($_POST, $errors);
			}
			else if ($ordered) {
				return confirm_form($_POST);
			}
			return order($dbh, $_POST);
		}

		return order_form();
	}
	catch (PDOException $e) {
		error_log('[Orders] Database error: ' . $e);
		return '<p>Tietokantavirhe.</p>';
	}

	if ($dbh !== FALSE) {
		$dbh->exec('UNLOCK TABLES;');
	}
}


function order ($dbh, $data) {
	$id = order_enter($dbh,
		$data['quantity'],
		$data['name'],
		$data['email'],
		$data['street'],
		$data['postcode'],
		$data['postoffice']);

	$order = order_by_id($dbh, $id);

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

	$mail_to = sprintf('%s <%s>',
			$order->name, $order->email);

	$mail_headers = sprintf("From: %s\r\n",
			Config::MAIL_FROM);

	$mail_text = fill_file(
			'templates/mail/order_confirmation.txt', $v);

	$mailed = mail($mail_to, Config::MAIL_SUBJECT,
			$mail_text, $mail_headers);

	if (!$mailed) {
		error_log(sprintf(
			'[Orders] Mailing confirmation for order %d to %s failed.',
			$order->id, $email));
	}

	return fill_file('templates/entered.html',
			array_map('htmlspecialchars', $v));
}

print form();


?>

