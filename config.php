<?php


class Config {
	const TICKETS_TOTAL = 500;
	const TICKET_PRICE = 5.00;

	const REFNUM_PREFIX = '12';

	const MAIL_SUBJECT = '[Yamacon] Tilausvahvistus';
	const MAIL_FROM = 'Yamaconin lipunmyynti <liput@yamacon.fi>';

	const DB_HOST = 'localhost';
	const DB_USER = 'yamacon_test';
	const DB_PASS = 'test';
	const DB_NAME = 'yamacon_test';

	const VALIDATE_FQDN = 'dev.ini.fi';
	const VALIDATE_FROM = 'kekimmo@ini.fi';
	const VALIDATE_SOCKET_TIMEOUT = 5;

	const TMPL_DIR = 'templates';

	const MAXLEN_NAME = 50; 
	const MAXLEN_EMAIL = 255; 
	const MAXLEN_STREET = 100; 
	const MAXLEN_POSTCODE = 5; 
	const MAXLEN_POSTOFFICE = 30;
}


?>
