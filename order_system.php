<?php


require_once('order_config.php');
require_once('db.php');
require_once('refnum.php');
require_once('queries.php');
require_once('validate.php');
require_once('template.php');
require_once('forms.php');
require_once('mail.php');
require_once('order.php');


function order_system () {
	try {
		$dbh = db_connect();

		$tickets_available = tickets_available($dbh);

		if ($tickets_available < 1) {
			return fill_file(Config::TMPL_DIR . '/sold_out.html');
		}

		$ordered = array_key_exists('order', $_POST);
		$confirmed = array_key_exists('confirm', $_POST);

		if ($ordered || $confirmed) {
			$errors = validate($dbh, $_POST, $ordered);
			if ($errors) {
				return order_form($_POST, $errors);
			}
			else if ($ordered) {
				$confirm_id = sha1($_POST['name'] . $_POST['street'] . time());
				return confirm_form($_POST, $confirm_id);
			}
			$id = order_already_entered($dbh, $_POST['confirm_id']);
			if ($id !== null) {
				// Order has already been entered
				// User probably refreshed the "order entered" page
				return order_entered(order_data(order_by_id($dbh, $id)));
			}

			// Enter the order
			return order($dbh, $_POST);
		}

		return order_form();
	}
	catch (PDOException $e) {
		error_log('[Orders] Database error: ' . $e);
		return '<p>Tietokantavirhe.</p>';
	}
}


function order ($dbh, $data) {
	$id = order_enter($dbh,
		$data['quantity'],
		$data['name'],
		$data['email'],
		$data['street'],
		$data['postcode'],
		$data['postoffice'],
		$data['confirm_id']);

	$order = order_by_id($dbh, $id);

	$v = order_data($order);
	$mail_text = fill_file(
			Config::TMPL_DIR . '/mail/order_entered.txt', $v);

	$mailed = order_mail($order->email, $order->name, Config::MAIL_ENTERED_SUBJECT, $mail_text);

	if (!$mailed) {
		error_log(sprintf(
			'[Orders] Mailing confirmation for order %d to %s failed.',
			$order->id, $order->email));
	}

	return order_entered($v);
}


function order_entered ($v) {
	return fill_file(Config::TMPL_DIR . '/entered.html',
			array_map('htmlspecialchars', $v));
}


?>
