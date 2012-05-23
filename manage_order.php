<?php


require_once('queries.php');
require_once('refnum.php');


function manage_order_list ($dbh) {
	function h ($text) {
		return htmlspecialchars($text);
	}

	function date_fmt ($time) {
		return ($time !== null
			//? htmlspecialchars(date('j.n.Y G:i:s', $time))
			? htmlspecialchars(date('j.n.', $time))
			: '<span class="time_null">EI</span>');
	}

	function event_fmt ($id, $event, $pos, $neg, $time) {
		$new_state = ($time === null);
		return date_fmt($time) . sprintf(
			' - <a class="action" href="manage.php?action=manage&id=%d&event=%s&state=%d">%s</a>',
			$id,
			htmlspecialchars($event),
			($new_state ? 1 : 0),
			($new_state ? $pos : $neg));
	}

	$orders = orders($dbh);
	$table = implode("\n", array_map(function ($order) {
		$cells = array_map(function ($field) {
			list($name, $content) = $field;
			return sprintf('<td class="%s">%s</td>',
				htmlspecialchars($name),
				$content);
		}, array(
			array('refnum', h(refnum_from_id($order->id))),
			array('quantity', $order->quantity),
			array('cost', Config::money_fmt($order->cost)),
			array('name', h($order->name)),
			array('email', h($order->email)),
			array('street', h(sprintf('%s, %s %s', $order->street,
			                                       $order->postcode,
                                                   $order->postoffice))),
			array('entered', date_fmt($order->entered)),
			array('paid', event_fmt($order->id, 'paid', 'Maksetuksi', 'Maksamattomaksi', $order->paid)),
			array('mailed', event_fmt($order->id, 'mailed', 'Lähetetyksi', 'Lähettämättömäksi', $order->mailed)),
		));
		return sprintf('<tr class="%s">%s</tr>',
			($order->mailed === null ? 'pending' : ''),
			implode('', $cells));
	}, $orders));

	return sprintf(
		'<table><thead><tr>
			<th>Viite</th>
			<th>Määrä</th>
			<th>Summa / €</th>
			<th>Nimi</th>
			<th>Sähköposti</th>
			<th>Osoite</th>
			<th>Kirjattu</th>
			<th>Maksu saatu</th>
			<th>Postitettu</th>
		</tr></thead><tbody>%s</tbody></table>',
		$table);
}


?>
