<?php


require_once('db.php');
require_once('queries.php');
require_once('order.php');
require_once('manage_order.php');
require_once('template.php');
require_once('mail.php');


session_start();

$dbh = db_connect();
$content = null;

if (array_key_exists('action', $_GET)) {
	$id = intval($_GET['id']);
	$action = $_GET['action'];
	if ($action === 'manage') {
		$valid_events = array('paid', 'mailed');
		$event = $_GET['event'];
		if (in_array($event, $valid_events)) {
			$state = ($_GET['state'] == 1);

			order_set_event($dbh, $id, $event, $state);

			$order = order_by_id($dbh, $id);

			if ($event === 'mailed' && $state) {
				$v = order_data($order);
				$mail_text = fill_file(
						Config::TMPL_DIR . '/mail/order_mailed.txt', $v);
				$subject = Config::MAIL_MAILED_SUBJECT;
	
				$mail_sent = order_mail($order->email, $order->name, $subject, $mail_text);
				if (!$mail_sent) {
					error_log(sprintf(
						'[Orders] Mailing notification "%s" for order %d to %s failed.',
						$event, $order->id, $order->email));
					$content = sprintf('<p>Sähköpostin lähettäminen osoitteeseen %s epäonnistui.</p>',
						htmlspecialchars($order->email));
				}
			}

			if ($content === null) {
				header('Location: manage.php');
				$content = '<p>Uudelleenohjataan...</p>';
			}
		}
		else {
			$content = sprintf('<p>Tuntematon tapahtuma <kbd>%s</kbd>.</p>',
				htmlspecialchars($event));
		}
	}	
	else {
		$content = sprintf('<p>Tuntematon toiminto <kbd>%s</kbd>.</p>',
			htmlspecialchars($action));
	}
}
else {
	$content = manage_order_list($dbh);
}


?>
<html>
	<head>
		<link rel="stylesheet" href="manage.css">
		<title>Tilausten käsittely</title>
	</head>
	<body>
		<h1>Tilausten käsittely</h1>
		<p>
			Kun tilaus merkitään lähetetyksi, tilaajalle lähtee automaattinen sähköposti-ilmoitus.
		</p>
		<?php print $content; ?>
	</body>
</html>
