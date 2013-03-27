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
			echo "$errno: $errstr";
			die();
		}
		return json_decode($body);
	}

	$json = getRoomData("EIT","1015");

	echo "\n";
	$errcode = $json->response->meta->Status;

	if ($errcode=="200") { // OK
		$classArray = $json->response->data->result;
		$schedule = array();
		foreach ($classArray as $obj) {
			$days = $obj->Days;
			$outDays = "";
			for ($i=0;$i<strlen($days);$i++) {
				if (substr($days,$i,2)=="Th") {
					$outDays .= "4";
					$i++;
				} else if (substr($days,$i,1)=="M") { $outDays .= "1"; }
				else if (substr($days,$i,1)=="T") { $outDays .= "2"; }
				else if (substr($days,$i,1)=="W") { $outDays .= "3"; }
				else if (substr($days,$i,1)=="F") { $outDays .= "5"; }
			}
			$instructor = $obj->Instructor;
			if ($instructor!="") {
				$a = explode(",",$instructor,2);
				$instructor = "$a[1] $a[0]";
			}

			$objSection = $obj->Section;
			$type = $section = "";
			if ($objSection!="") {
				$a = explode(" ",$objSection,2);
				$type = $a[0];
				$section = $a[1];
			}


			$newSched = array(
				"course" => "$obj->DeptAcronym $obj->Number",
				"title" => $obj->Title,
				"instructor" => $instructor,
				"type" => $type,
				"section" => $section,
				"start" => str_replace(":","",$obj->EndTime),
				"end" => str_replace(":","",$obj->StartTime),
				"days" => $outDays,
				"id" => $obj->ID,
				"term" => $obj->Term,
			);

			var_dump($obj);
			echo "\n";

			$schedule[] = $newSched;
		}
		var_dump($schedule);
	} else {

	}

?>