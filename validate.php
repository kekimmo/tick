<?php


function missing ($data) {
	return $data === null || $data === '';
}


function shorter_than ($min) {
	return function ($data) use ($min) {
		return strlen($data) < $min;
	};
}

function longer_than ($max) {
	return function ($data) use ($max) {
		return strlen($data) > $max;
	};
}


function does_not_contain ($s) {
	return function ($data) use ($s) {
		return strpos($data, $s) === FALSE;
	};
}


function not_numbers ($data) {
	return !ctype_digit($data);
}


function assuming_numbers ($test) {
	return function ($data) use ($test) {
		return !not_numbers($data) && $test(intval($data));
	};
}


function assuming_exists ($test) {
	return function ($data) use ($test) {
		return !missing($data) && $test($data);
	};
}


function assuming ($condition, $test) {
	return function ($data) use ($condition, $test) {
		return !$condition($data) && $test($data);
	};
}


function smtp_ok ($email) {
	$pos = strpos($email, '@');
	if ($pos === FALSE || $pos == strlen($email) - 1) {
		return false;
	}

	$hostname = substr($email, $pos + 1);
	$mxs = array();
	$found = getmxrr($hostname, $mxs);
	if (!$found) {
		$mxs = array($hostname);
	}
	//$mxs = array('localhost');

	foreach ($mxs as $host) {
		$errno = 0;
		$errstr = '';
		$s = fsockopen($host, 25, $errno, $errstr, Config::VALIDATE_SOCKET_TIMEOUT);
		if ($s !== FALSE) {
			$ok = smtp_ok_at_host($s, $email);
			fclose($s);
			if ($ok) {
				//printf('%s: OK!<br>', $host);
				return true;
			}
		}
		//printf('%s: FAIL<br>', $host);
	}

	return false;
}


function smtp_ok_at_host ($s, $email) {
	$w = function ($command) use ($s) {
		return s_write_and_expect($s, $command, 250);
	};

	$ok = (s_expect($s, 220)
		&& $w(sprintf("HELO %s\r\n", Config::VALIDATE_FQDN))
		&& $w(sprintf("MAIL FROM:<>\r\n", Config::VALIDATE_FROM))
		&& s_write($s, sprintf("RCPT TO:<%s>\r\n", $email))
		&& s_expect_not($s, 550));

	s_write($s, 'QUIT\r\n');
	
	return $ok;
}


function s_write_and_expect ($socket, $command, $reply) {
	return s_write($socket, $command) && s_expect($socket, $reply);
}


function s_write ($socket, $command) {
	$command_len = strlen($command);
	$written = 0;
	while ($written < $command_len) {
		$ret = fwrite($socket, substr($command, $written));
		if ($ret === FALSE) {
			return false;
		}
		$written += $ret;
	}
	//print '&gt;' . htmlspecialchars($command) . '<br>';
	return fflush($socket);
}


function s_expect_not ($socket, $reply) {
	$code = s_get_reply($socket);
	return ($code !== FALSE && $code !== $reply);
}


function s_expect ($socket, $reply) {
	$code = s_get_reply($socket);
	return ($code === $reply);
}


function s_get_reply ($socket) {
	$line = fgets($socket, 1024);
	//print '&lt;' . htmlspecialchars($line) . '<br>';
	if ($line !== FALSE) {
		$code_str = substr($line, 0, 3);
		if (ctype_digit($code_str)) {
			$code = intval($code_str);
			return $code;
		}
	}
	return FALSE;
}


function validate ($dbh, $data, $smtp_check = true) {
	$tickets_available = tickets_available($dbh);

	$v = array(
		array('name', array(
			'missing' => 'missing',
			'too_long' => longer_than(Config::MAXLEN_NAME)
		)),

		array('email', array(
			'missing' => 'missing',
			'too_long' => longer_than(Config::MAXLEN_EMAIL),
			//'no_at' => assuming_exists(does_not_contain('@')),
			'not_working' => assuming_exists(function ($email) use ($smtp_check) {
				return ($smtp_check
					? !smtp_ok($email)
					: false);
			})
		)),

		array('street', array(
			'missing' => 'missing',
			'too_long' => longer_than(Config::MAXLEN_STREET)
		)),
	
		array('postcode', array(
			'missing' => 'missing',
			'invalid' => assuming_exists(function ($code) {
				return strlen($code) != 5 || not_numbers($code);
			})
		)),

		array('postoffice', array(
			'missing' => 'missing',
			'too_long' => longer_than(Config::MAXLEN_POSTOFFICE)
		)),
	
		array('quantity', array(
			'missing' => 'missing',
			'not_numbers' => assuming_exists('not_numbers'),
			'zero' => assuming_exists(assuming_numbers(
					function ($n) {
						return $n < 1;
					})),
			'too_many' => assuming_exists(assuming_numbers(
					function ($n) use ($tickets_available) {
						return $n > $tickets_available;
					}))
		))
	);

	$errors = array();

	foreach ($v as $arr) {
		list($fields, $tests) = $arr;

		if (!is_array($fields)) {
			$fields = array($fields);
		}

		$field_values = array_map(function ($field) use ($data) {
			if (array_key_exists($field, $data)) {
				return $data[$field];
			}
			else {
				return null;
			}
		}, $fields);

		$failed = array_filter($tests, function ($test) use ($field_values) {
			return call_user_func_array($test, $field_values);
		});

		if (!empty($failed)) {
			foreach ($fields as $field) {
				if (!array_key_exists($field, $errors)) {
					$errors[$field] = array();
				}
				$errors[$field] = array_merge($errors[$field], array_keys($failed));
			}
		}
	}

	return $errors;
}


?>
