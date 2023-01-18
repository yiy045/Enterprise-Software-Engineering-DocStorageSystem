<?php
/*api_query_files.php - will query from the professors database for the files*/
include("/var/www/html/functions.php");
$dblink=db_connect("api_sessions");

/*username*/
$username="yiy045";

/*regex*/
$regex = "/\/storage\/files\/[a-zA-Z]{3}\d{3}\/\d+-[a-zA-Z]+-\d{8}_\d{2}_\d{2}_\d{2}.[a-zA-Z]+/";

/*gets session id*/
$sql = "SELECT `session_id` FROM `sessions` WHERE `auto_id` = (SELECT MAX(`auto_id`) FROM `sessions`) AND `status` = 'open'";
$result=$dblink->query($sql) or
	die("Something went wrong with $sql<br>".$dblink->error);
$data=$result->fetch_array(MYSQLI_ASSOC);
$sid=$data['session_id'];

/*curl command*/
$data = "sid=$sid&uid=$username";
$ch=curl_init('.../api/query_files');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'content-type: application/x-www-form-urlencoded', 
	'content-length: ' . strlen($data)));

$time_start = microtime(true);
$result = curl_exec($ch);
$time_end = microtime(true);
$exec_time = ($time_end - $time_start)/60;
curl_close($ch);
$cinfo=json_decode($result,true);

/*if the status is OK*/
if($cinfo[0] == "Status: OK") {
	/*if there is no action to be taken*/
	if($cinfo[1] == "Action: None") {
		//echo "No new files to import\r\n";
		//echo "Query execution time: $exec_time";
		$sql = "UPDATE `sessions` SET `isFiles`='no' WHERE `auto_id` = (SELECT MAX(`auto_id`)) AND `status` = 'open'";
		$dblink->query($sql) or 
			die("Something went wrong with $sql<br>".$dblink->error);
	}
	
	/*explodes name*/
	else {
		$temp = explode(":", $cinfo[1]);
		$files = explode(",", $temp[1]);
		echo "# of files to import: ".count($files)."\r\n";
		echo "Files:\r\n";
		
		/*goes through list of files and inserts name and status as 'not used' into DB*/
		foreach($files as $key=>$value) {
			$files = array_unique($files);
			if(preg_match($regex, $value)) {
				echo $value."\r\n";
				$sql = "INSERT INTO `file_info` (`session_id`, `file_name`,`status`) VALUES ('$sid', '$value','not used')";
				$dblink->query($sql) or 
					die("Something went wrong with $sql<br>".$dblink->error);
				
			}
			else {
				$sql= "INSERT INTO `file_error_logs` (`session_id`, `file_name`, `error_message`) VALUES ('$sid', '$value', 'regex error')";
				continue;
			}
		}
		echo "Query execution time: $exec_time\r\n";
	}
}

?>