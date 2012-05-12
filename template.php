<?php


function fill ($template, $vars = array()) {
	$from = array();
	$to = array();

	foreach ($vars as $key => $var) {
		$from[] = '%' . $key . '%';
		$to[] = $var;
	}

	return str_replace($from, $to, $template);
}


function fill_file ($file, $vars = array()) {
	return fill(file_get_contents($file), $vars);
}


?>
