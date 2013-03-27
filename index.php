<?php
	header("Content-type: application/json;");

	function getRoomData($building,$number) {
		$ini = parse_ini_file("config.ini");
		$uwkey = $ini["uwapikey"];
		$building = strtoupper($building);
		$body = '';
		if (($io=fsockopen('api.uwaterloo.ca', 80, $errno, $errstr, 30))) {
			$sr  = "GET /public/v1/?key=$uwkey&service=CourseFromRoom&q=$building$number HTTP/1.1\r\n";
			$sr .= "Host: api.uwaterloo.ca\r\n";
			$sr .= "Connection: close\r\n\r\n";
			fputs($io, $sr);
			$header = '';
			do {
				$header .= fgets($io, 128);
			} while (strpos($header, "\r\n\r\n" ) === false);
			while (!feof($io)) {
				$body .= fgets($io, 128);
			}
			fclose($io);
		} else {
			die();
		}
		return json_decode($body);
	}

	$json = getRoomData("EIT","1015");

	var_dump($json);

?>