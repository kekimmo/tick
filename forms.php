<?php

require_once('order_config.php');
require_once('validate.php');
require_once('template.php');
require_once('order.php');


function values_by_keys ($keys, $arr) {
	$result = array();
	foreach ($keys as $key) {
		if (array_key_exists($key, $arr)) {
			$result[$key] = $arr[$key];
		}
	}
	return $result;
}


function confirm_form ($data, $confirm_id) {
	$keys = array(
			'quantity',
			'name',
			'email',
			'street',
			'postcode',
			'postoffice',
			'confirm_id'
	);
	$data['confirm_id'] = $confirm_id;

	$v = values_by_keys($keys, $data);
	$v['cost'] = Config::money_fmt(Order::cost(intval($data['quantity'])));
	$v['postage'] = Config::money_fmt(Config::POSTAGE);
	$v = array_map('htmlspecialchars', $v);

	$html = fill_file(Config::TMPL_DIR . '/confirm.html', $v)
		  . '<form method="post" action="">'
		  . join(array_map(function ($key) use ($data) {
				return input('hidden', $key, null, null, htmlspecialchars($data[$key]));
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
			array('name', 'Tilaajan nimi', Config::MAXLEN_NAME, ''),
			array('email', 'Sähköpostiosoite', Config::MAXLEN_EMAIL, ''),
			array('street', 'Katuosoite', Config::MAXLEN_STREET, ''),
			array('postcode', 'Postinumero', Config::MAXLEN_POSTCODE, ''),
			array('postoffice', 'Postitoimipaikka', Config::MAXLEN_POSTOFFICE, '')
	) as $params) {
		list($id, $label, $maxlength, $default) = $params;
		$has_errors = array_key_exists($id, $errors);
		$tf[$id] = textfield($id, $label, $maxlength,
			$has_errors ? 'error' : null, $pf($id, $default))
			.
			($has_errors
				? sprintf('<span class="errors">%s</span>',
					htmlspecialchars(join(' | ', array_map(
						function ($error) use ($id) {
							return Config::form_error($id, $error);
						}, $errors[$id]))))
				: '');
	}

	$tf['submit'] = input('submit', 'order', null, 'submit', 'Seuraava');
	$tf['price'] = htmlspecialchars(Config::money_fmt(Config::TICKET_PRICE));
	$tf['postage'] = htmlspecialchars(Config::money_fmt(Config::POSTAGE));

	return fill_file(Config::TMPL_DIR . '/order.html', $tf);

	$html = <<<EOT
<form class="order" method="post" action="">
	<span class="field">${tf['quantity']}</span><br />
	<span class="field">${tf['name']}</span><br />
	<span class="field">${tf['email']}</span><br />
	<span class="field">${tf['street']}</span><br />
	<span class="field">${tf['postcode']}</span><br />
	<span class="field">${tf['postoffice']}</span><br />
	${submit}
</form>
EOT;

	return $html;
}


function textfield ($id, $label, $maxlength = null, $class = null, $value = null) {
	return input('text', $id, $label, $class, $value,
		($maxlength !== null
			? sprintf('maxlength="%d"', $maxlength)
			: null));
}


function input ($type, $id, $label = null, $class = null, $value = null, $attrs = null) {
	$html = '';
	if ($label !== null)
		$html .= sprintf('<label for="%1$s">%2$s</label>',
			$id, htmlspecialchars($label));
	$html .= sprintf('<input type="%1$s" name="%2$s" id="%2$s"',
			$type, $id);
	if ($class !== null)
		$html .= sprintf(' class="%s"', htmlspecialchars($class));
	if ($value !== null)
		$html .= sprintf(' value="%s"', htmlspecialchars($value));
	if ($attrs !== null)
		$html .= ' ' . $attrs;
	return $html . '>';
}

?>
