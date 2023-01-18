<?php
/*api_close_session.php - Will close the session*/
include("/var/www/html/functions.php");
$dblink=db_connect("api_sessions"); 

/*username*/
$username="yiy045";
/*SQL COMMAND TO GET SIDs*/
$sql = "SELECT `session_id` FROM `sessions` WHERE `auto_id` = (SELECT MAX(`auto_id`) FROM `sessions`) AND `status` = 'open'";
$result=$dblink->query($sql) or
	die("Something went wrong with $sql<br>".$dblink->error);
$data=$result->fetch_array(MYSQLI_ASSOC);
$sid=$data['session_id'];

echo "Session id: \"$sid\"\r\n";

if($sid != ""){
	/*curl command for site*/
	$data = "sid=$sid&uid=$username";
    $ch=curl_init('.../api/close_session');
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

    /*check if session actually closed*/
    if($cinfo[0] == "Status: OK"){
		echo "Session Closed Successfully!\r\n";
        echo "Session id: $sid\r\n";
        echo "Close Session execution time: $exec_time\r\n";
        /*set status to close*/
        $sql = "UPDATE `sessions` SET `status` = 'closed' WHERE `session_id` = '$sid'"; 
        $result=$dblink->query($sql) or
			die("Something went wrong with $sql<br>".$dblink->error);
        }
	/*if failed*/
	else {
        echo "$cinfo[0]";
        echo "\r\n";
        echo "$cinfo[1]";
        echo "\r\n";
        echo "$cinfo[2]";
        echo "\r\n";
        /*send error to db*/
        $sql = "INSERT INTO `error_logs` (`error_message`,`action`) VALUES ('$cinfo[1]','$cinfo[2]')";
        $dblink->query($sql) or
        die("Something went wrong with $sql<br>".$dblink->error);
	}
}
/*if no sessions are currently open*/
else {
	echo "No currently open sessions!\r\n";
	$sql = "INSERT INTO `error_logs` (`error_message`,`action`) VALUES ('No open SIDs Found','Check code')";
	$dblink->query($sql) or
		die("Something went wrong with $sql<br>".$dblink->error);
}

?>