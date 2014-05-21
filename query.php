<?php

/**
* This static class is called through AJAX and connects to the UW API website, gets room 
* data for the specified room, and then gives a friendly JSON output.
* @author Ankit Sardesai
* @copyright 2013 Ankit Sardesai
*/
class QueryUW {

	/**
	* Outputs an easy to interpret error object given the input error
	* @param integer $number The error number
	* @param string $message The error message
	* @return object A clean error object
	*/
	private static function error_object($number,$message) {
		return array(
			"error" => true,
			"number" => $number,
			"message" => $message
		);
	}

	/**
	* Gets room data from UWaterloo API website and returns an object interpretation 
	* of the returned JSON code.
	* @param string $building The building being queried
	* @param integer $number The room number being queried
	* @return object JSON data containing the schedule of the room
	*/
	private static function get_room_data($building,$number) {
		if ($building=="" && $number=="")
			return self::error_object(0, "You did not enter anything!");
		else if ($building=="" || strlen($building)>3)
			return self::error_object(1, "Invalid building code!");
		else if ($number=="" || $number==0 || strlen($building)>4)
			return self::error_object(2, "Invalid room number!");
		$ini = parse_ini_file("config.ini");
		$uwkey = $ini["uwapikey"];
		$building = strtoupper($building);
		$body = $header = "";
		if (($io=@fsockopen('api.uwaterloo.ca', 80, $errno, $errstr, 5))) {
			$sr  = "GET /v2/buildings/$building/$number/courses.json?key=$uwkey HTTP/1.1\r\n";
			$sr .= "Host: api.uwaterloo.ca\r\n";
			$sr .= "Connection: close\r\n\r\n";
			fwrite($io, $sr);
			$response = "";
			while(!feof($io)) $response .= fgets($io);
			list($header, $body) = explode("\r\n\r\n", $response, 2);
			
			$lpos = strpos($body, '{');
			$bodylength = strrpos($body, '}') - strlen($body) + 1;
			$bodylength = $bodylength === 0 ? strlen($body) : $bodylength;

			$body = substr($body, $lpos, $bodylength);

			fclose($io);
		} else return self::error_object(5, "Networking Error $errno: <ul><li>$errstr</li></ul>");

		return json_decode($body);
	}

	/**
	* Processes the output of get_room_data and converts it into a cleaner and easier format
	* @param object $json JSON object containing room data returned directly from the API
	* @return object Processed, cleaner version of the input object
	*/
	private static function process_room_data($json) {
		if (is_array($json)) return $json;
		$errcode = $json->meta->status;
		$errmsg = $json->meta->message;
		if ($errcode==200) { // OK
			$classArray = $json->data;
			if (count($classArray)==0)
				return self::error_object(4, "Not found! This could be due to the following: 
					<ul><li>This room does not exist</li><li>There are no classes in this 
					room</li><li>The classes haven't been put in the database</li></ul>");
			$schedule = array();
			foreach ($classArray as $obj) {
				$days = $obj->weekdays;
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

				$instructor = "";
				$instructorArray = $obj->instructors;
				if (!empty($instructorArray)) {
					$a = explode(",",$instructorArray[0],2);
					$instructor = "$a[1] $a[0]";
				}

				$objSection = $obj->section;
				$type = $section = "";
				if ($objSection!="") {
					$a = explode(" ",$objSection,2);
					$type = $a[0];
					$section = $a[1];
				}

				$newSched = array(
					"course" => "$obj->subject $obj->catalog_number",
					"title" => $obj->title,
					"instructor" => $instructor,
					"type" => $type,
					"section" => $section,
					"start" => str_replace(":","",$obj->start_time),
					"end" => str_replace(":","",$obj->end_time),
					"days" => $outDays,
					"id" => $obj->class_number,
					"term" => $obj->term,
				);

				$schedule[] = $newSched;
			}
			return $schedule;
		} else if ($errcode==204) {
			return self::error_object(4, "Not found! This could be due to the following: 
								<ul><li>This room does not exist</li><li>There are no classes in this 
								room</li><li>The classes haven't been put in the database</li></ul>");
		} else {
			return self::error_object(6, "HTTP Error $errcode: <ul><li> $errmsg </li></ul>");
		}
	}

	/**
	* Main function that runs all functions and returns JSON
	*/
	public static function init() {
		header("Content-type: application/json;");
		$building = $_POST["b"];
		$room = $_POST["r"];
		echo json_encode(self::process_room_data(self::get_room_data($building,$room)));
	}

} // End QueryUW

QueryUW::init();

?>