<?php


class Config {
	const TICKETS_TOTAL = 800;
	// Prices in cents to avoid floating point problems
	// Note: No initial zeros, or the number will be interpreted as octal
	const TICKET_PRICE = 500;
	const POSTAGE = 90;
	// Maximum amount of tickets in a single order
	const MAX_QUANTITY = 20;

	const ACCOUNT_NUMBER = 'FI32 5238 3650 0176 73';
	const ACCOUNT_NUMBER_NUMERIC = '32 5238 3650 0176 73';
	const REFNUM_PREFIX = '12';
	static function due ($order_entered) {
		return strtotime('+2 weeks', $order_entered);
	}

	const MAIL_SUBJECT = '[Yamacon] Tilausvahvistus';
	const MAIL_FROM = 'Yamaconin lipunmyynti <liput@yamacon.fi>';

	const DB_HOST = 'localhost';
	const DB_USER = 'yamacon_test';
	const DB_PASS = 'test';
	const DB_NAME = 'yamacon_test';

	const VALIDATE_FQDN = 'yamacon.fi';
	const VALIDATE_FROM = '<>';
	const VALIDATE_SOCKET_TIMEOUT = 5;

	const BASE_DIR = '/srv/http/yamacon.fi/tick';
	const TMPL_DIR = '/srv/http/yamacon.fi/tick/templates';

	const MAXLEN_NAME = 50; 
	const MAXLEN_EMAIL = 255; 
	const MAXLEN_STREET = 100; 
	const MAXLEN_POSTCODE = 5; 
	const MAXLEN_POSTOFFICE = 30;

	static function money_fmt ($amount) {
		$eur = $amount / 100;
		$c = $amount % 100;
		return sprintf('%d,%02d', $eur, $c);
	}

	static function form_error ($id, $error) {
		$es = array(
			'quantity' => array(
				'missing' => 'Anna lippumäärä.',
				'not_numbers' => 'Anna positiivinen luku (esim. 2).',
				'zero' => 'Tilaa ainakin yksi lippu.',
				'too_many' => sprintf('Maksimimäärä on %d lippua.', Config::MAX_QUANTITY),
				'not_available' => 'Lippuja ei ole jäljellä tarpeeksi.'
			),

			'name' => array(
				'missing' => 'Tarvitsemme nimesi.',
				'too_long' => 'Käytä lyhyempää muotoa.'
			),	

			'email' => array(
				'missing' => 'Anna mailiosoite (emme spämmää!)',
				'too_long' => 'Liian pitkä.',
				'not_working' => 'Osoite ei toimi.'
			),

			'street' => array(
				'missing' => 'Tarvitsemme osoitteesi.',
				'too_long' => 'Liian pitkä.'
			),

			'postcode' => array(
				'missing' => 'Tarvitsemme postinumerosi.',
				'invalid' => 'Viisi numeroa, kiitos.'
			),

			'postoffice' => array(
				'missing' => 'Anna varmuuden vuoksi tämäkin.',
				'too_long' => 'Liian pitkä.'
			)
		);
				
		if (array_key_exists($id, $es) &&
			array_key_exists($error, $es[$id])) {
			return $es[$id][$error];
		}
		else {
			return sprintf('<%s:%s>', $id, $error);
		}
	}
};


?>
