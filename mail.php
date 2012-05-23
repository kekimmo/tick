<?php


require_once('order_config.php');


function order_mail ($to_email, $to_name, $subject, $text) {
	$to = sprintf('=?UTF-8?B?%s?= <%s>',
			base64_encode($to_name), $to_email);

	$headers = sprintf(
		"MIME-Version: 1.0\r\n" .
		"Content-Type: text/plain; charset=UTF-8\r\n" .
		"From: %s\r\n",
			Config::MAIL_FROM);


	return mail($to, '=?UTF-8?B?' . base64_encode($subject) . '?=',
			$text, $headers);
}


?>
