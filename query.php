<?php

/**
* This file connects to the UW API website, gets room data for the specified room, and then gives a friendly JSON output.
* @author Ankit Sardesai
* @copyright 2013 Ankit Sardesai
*/

/**
* Gets room data from UWaterloo API website and returns an object interpretation of the returned JSON code.
* @param string $building The building being queried
* @param integer $number The room number being queried
* @return object JSON data containing the schedule of the room
*/
function getRoomData($building,$number) {
	if ($building=="" && $number=="") {
		return array(
			"error" => true,
			"number" => 0,
			"message" => "You did not enter anything!",
		);
	} else if ($building=="" || strlen($building)>3) {
		return array(
			"error" => true,
			"number" => 1,
			"message" => "Invalid building code!",
		);
	} else if ($number=="" || $number==0 || strlen($building)>4) {
		return array(
			"error" => true,
			"number" => 2,
			"message" => "Invalid room number!",
		);
	}
	$ini = parse_ini_file("config.ini");
	$uwkey = $ini["uwapikey"];
	$building = strtoupper($building);
	$body = '';
	if (($io=@fsockopen('api.uwaterloo.ca', 80, $errno, $errstr, 5))) {
		$sr  = "GET /public/v1/?key=$uwkey&service=CourseFromRoom&output=json&q=$building-$number HTTP/1.1\r\n";
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
		return array(
			"error" => true,
			"number" => 5,
			"message" => "Networking Error $errno: <ul><li>$errstr</li></ul>",
		);
	}
	return json_decode($body);
}

/**
* Processes the output of getRoomData and converts it into a cleaner and easier format
* @param object $json JSON object containing room data returned directly from the API
* @return object Processed, cleaner version of the input object
*/
function processRoomData($json) {
	if (is_array($json)) return $json;
	$errcode = $json->response->meta->Status;
	if ($errcode=="200") { // OK
		$classArray = $json->response->data->result;
		if (count($classArray)==0) {
			return array(
				"error" => true,
				"number" => 4,
				"message" => "Not found! This could be due to the following: <ul><li>This 
					room does not exist</li><li>There are no classes in this room</li><li>The 
					classes haven't been put in the database</li></ul>",
			);
		}
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
				"start" => str_replace(":","",$obj->StartTime),
				"end" => str_replace(":","",$obj->EndTime),
				"days" => $outDays,
				"id" => $obj->ID,
				"term" => $obj->Term,
			);

			$schedule[] = $newSched;
		}
		return $schedule;
	} else {
		return array(
			"error" => true,
			"number" => 6,
			"message" => "HTTP Error $errcode: <ul><li> $json->response->meta->Message </li></ul>",
		);
	}
}

header("Content-type: application/json;");
$building = $_POST["b"];
$room = $_POST["r"];
echo json_encode(processRoomData(getRoomData($building,$room)));

?>