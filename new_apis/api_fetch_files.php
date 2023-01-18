<?php
/*api_fetch_files.php - will look through file_info db and pull the names from the table*/
include("/var/www/html/functions.php");
$dblink=db_connect("api_sessions");
$username="yiy045";

/*get current SID*/
$sql = "SELECT `session_id` FROM `sessions` WHERE `auto_id` = (SELECT MAX(`auto_id`) FROM `sessions`) AND `status` = 'open'";
$result=$dblink->query($sql) or
	die("Something went wrong with $sql<br>".$dblink->error);
$data=$result->fetch_array(MYSQLI_ASSOC);
$sid=$data['session_id'];

/*get file names from file_info*/
$sql = "SELECT `file_name` FROM `file_info` WHERE `status` = 'not used'";
$result=$dblink->query($sql) or
	die("Something went wrong with $sql<br>".$dblink->error);
/*explodes*/
while($data=$result->fetch_array(MYSQLI_ASSOC)) {
	$file_name = $data['file_name'];
	$temp = explode("/", $file_name);
	$file = $temp[4];
	//echo "File: $file\r\n";
	$request = "sid=$sid&uid=$username&fid=$file";
	
	/*curl to request the files*/
	$ch = curl_init('.../api/request_file');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'content-type: application/x-www-form-urlencoded',
	'content-length: ' . strlen($request)));
	
	$time_start = microtime(true);
	$fetch_result = curl_exec($ch);
	$time_end = microtime(true);
	$exec_time = ($time_end - $time_start)/60;
	$content = $fetch_result;
	curl_close($ch);
	
	/*writing to file system*/
	$fp = fopen("/var/www/html/recieved_files/$file", "wb");
	fwrite($fp, $content);
	fclose($fp);
	//echo "$file written to file system\r\n";
	
	/*changes status from not used to 'used'*/
	$sql = "UPDATE `file_info` SET `status`='used' WHERE `status`='not used'";
	$dblink->query($sql) or 
		die("Something went wrong with $sql<br>".$dblink->error);
}
?>