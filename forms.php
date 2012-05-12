<?php

require_once('config.php');
require_once('validate.php');
require_once('template.php');


function values_by_keys ($keys, $arr) {
	$result = array();
	foreach ($keys as $key) {
		if (array_key_exists($key, $arr)) {
			$result[$key] = $arr[$key];
		}
	}
	return $result;
}


function confirm_form ($data) {
	$keys = array(
			'quantity',
			'name',
			'email',
			'street',
			'postcode',
			'postoffice'
	);
	$v = values_by_keys($keys, $data);
	$v['cost'] = sprintf('%.2f',
			intval($data['quantity']) * Config::TICKET_PRICE);
	$v = array_map('htmlspecialchars', $v);

	$html = fill_file('templates/confirm.html', $v)
		  . '<form method="post" action="">'
		  . join(array_map(function ($key) use ($data) {
				return input('hidden', $key, null, null, $data[$key]);
			}, $keys))
		  . input('submit', 'confirm', null, null, 'Vahvista')
		  . '</form>';

	return $html;
}


function order_form ($prefilled = array(), $errors = array()) {
	$pf = function ($key, $otherwise) use ($prefilled) {
		return array_key_exists($key, $prefilled)
			? $prefilled[$key]
			: $otherwise;
	};

	$tf = array();
	foreach (array(
			array('quantity', 'Lippujen määrä', 2, 1),
			array('name', 'Nimi', Config::MAXLEN_NAME, ''),
			array('email', 'Sähköpostiosoite', Config::MAXLEN_EMAIL, ''),
			array('street', 'Katuosoite', Config::MAXLEN_STREET, ''),
			array('postcode', 'Postinumero', Config::MAXLEN_POSTCODE, ''),
			array('postoffice', 'Postitoimipaikka', Config::MAXLEN_POSTOFFICE, '')
	) as $params) {
		list($id, $label, $maxlength, $default) = $params;
		$tf[$id] = textfield($id, $label, $maxlength,
			null, $pf($id, $default))
			.
			(array_key_exists($id, $errors)
				? htmlspecialchars(join(' | ', $errors[$id]))
				: '');
	}

	$submit = input('submit', 'order', null, null, 'Seuraava');

	$html = <<<EOT
<form method="post" action="">
	${tf['quantity']}<br />
	${tf['name']}<br />
	${tf['email']}<br />
	${tf['street']}<br />
	${tf['postcode']}<br />
	${tf['postoffice']}<br />
	$submit
</form>
EOT;

	return $html;
}


function textfield ($id, $label, $maxlength = null, $style = null, $value = null) {
	return input('text', $id, $label, $style, $value,
		($maxlength !== null
			? sprintf('maxlength="%d"', $maxlength)
			: null));
}


function input ($type, $id, $label = null, $style = null, $value = null, $attrs = null) {
	$html = '';
	if ($label !== null)
		$html .= sprintf('<label for="%1$s">%2$s</label>',
			$id, htmlspecialchars($label));
	$html .= sprintf('<input type="%1$s" name="%2$s" id="%2$s"',
			$type, $id);
	if ($style !== null)
		$html .= sprintf(' style="%s"', htmlspecialchars($style));
	if ($value !== null)
		$html .= sprintf(' value="%s"', htmlspecialchars($value));
	if ($attrs !== null)
		$html .= ' ' . $attrs;
	return $html . '>';
}

