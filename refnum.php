<?php


require_once('order_config.php');


class BarcodeException extends Exception {};


// <http://www.fkl.fi/teemasivut/sepa/tekninen_dokumentaatio/Dokumentit/Pankkiviivakoodi_v052.pdf>
// Code version 4 (IBAN account number, national reference number)
function barcode ($sum, $refnum, $due, $iban_numeric = null) {
	if ($iban_numeric === null) {
		$iban_numeric = Config::ACCOUNT_NUMBER_NUMERIC;
	}
	// Remove whitespace
	$iban_numeric = str_replace(' ', '', $iban_numeric);
	$refnum = str_replace(' ', '', $refnum);

	// Specified lengths for sanity checks
	$lengths = array(
		'version'      => 1,
		'iban_numeric' => 16,
		'eur'          => 6,
		'c'            => 2,
		'reserved'     => 3,
		'refnum'       => 20,
		'due'          => 6
	);

	$parts = array(
		'version'      => '4',
		'iban_numeric' => $iban_numeric,
		'eur'          => sprintf('%06d', $sum / 100),
		'c'            => sprintf('%02d', $sum % 100), 
		'reserved'     => '000',
		'refnum'       => str_pad($refnum, 20, '0', STR_PAD_LEFT),
		'due'          => date('ymd', $due)
	);

	// Construct code parts
	foreach ($parts as $part => $value) {
		$required_length = $lengths[$part];
		if (strlen($value) != $required_length) {
			throw new BarcodeException(sprintf(
				'Computed value %s for %s is not of required length %d.',
				$value, $part, $required_length));
		}
	}

	return join('', $parts);
}


class RefnumException extends Exception {};


function refnum_from_id ($id) {
	$MIN_LEN = 4;
	$MAX_LEN = 20;
	$MAX_ID = 999999;

	if (!is_int($id)) {
		throw new RefnumException(sprintf('Id not an integer: %s', $id));
	}
	if ($id < 0 || $id > $MAX_ID) {
		throw new RefnumException(sprintf('Id not between 0 and %d: %d',
					$MAX_ID, $id));
	}

	$str_id = sprintf('%d', $id);

	$rn = Config::REFNUM_PREFIX . str_pad($str_id,
			$MIN_LEN - strlen(Config::REFNUM_PREFIX) - 1, '0', STR_PAD_LEFT);

	$rn .= refnum_checksum($rn);

	if (strlen($rn) > $MAX_LEN) {
		throw new RefnumException(sprintf('Reference number too long: %s', $rn));
	}

	return $rn;
}


function refnum_to_id ($refnum) {
	if (!is_string($refnum)) {
		$refnum = sprintf("%s", $refnum);
	}
	$refnum = str_replace(' ', '', $refnum); // remove possible spacing
	$prefix = Config::REFNUM_PREFIX;
	if (!ctype_digit($refnum) || strpos($refnum, $prefix) !== 0) {
		throw new RefnumException(sprintf('Invalid reference number: %s', $refnum));
	}

	if (!refnum_checksum_valid($refnum)) {
		throw new RefnumException(sprintf(
					'Reference number with invalid checksum: %s',
					$refnum));
	}

	// <prefix><id><checksum>
	$ref_len = strlen($refnum);
	$pre_len = strlen($prefix);
	$id_len = $ref_len - $pre_len - 1;

	$id = intval(substr($refnum, $pre_len, $id_len));
	return $id;
}


function refnum_checksum_valid ($refnum) {
	$ref_len = strlen($refnum);
	$ref_without_check = substr($refnum, 0, $ref_len - 1);
	$check = intval($refnum[$ref_len - 1]);
	$computed_check = refnum_checksum($ref_without_check);
	return ($computed_check === $check);
}


function refnum_checksum ($refnum) {
	$refnum = sprintf('%s', $refnum);

	$sum = 0;

	$next_weight = array(
			7 => 3,
			3 => 1,
			1 => 7
	);

	$weight = 7;

	for ($i = strlen($refnum) - 1; $i >= 0; --$i) {
		$sum += $weight * intval($refnum[$i]);
		$weight = $next_weight[$weight];
	}

	$check = 10 - ($sum % 10);

	if ($check == 10) {
		$check = 0;
	}

	return $check;
}


?>
