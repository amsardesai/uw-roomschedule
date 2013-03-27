<?php
	header("Content-type: application/json;");

	$ini = parse_ini_file("config.ini");

	define("API_KEY", $ini["uwapikey"]);
	$key = API_KEY;
	$json = '';
	if (($io=fsockopen('api.uwaterloo.ca', 80, $errno, $errstr, 5))) {
		$sr  = "GET /public/v1/?key=$key&service=CourseFromRoom&q=EIT1015 HTTP/1.1\r\n";
		$sr .= "Host: api.uwaterloo.ca\r\n";
		$sr .= "Connection: close\r\n\r\n";
		fputs($io, $sr);
		$header = '';
		do {
			$header .= fgets($io, 128);
		} while (strpos($header, "\r\n\r\n" ) === false);
		while (!feof($io)) {
			$json .= fgets($io, 128);
		}
		echo $json;
		fclose($io);
	}


?>