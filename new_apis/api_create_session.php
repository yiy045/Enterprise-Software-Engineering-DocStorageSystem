<?php
/*api_create_session.php - Will create session*/
/*functions.php*/
include("/var/www/html/functions.php");
/*db_connect*/
$dblink=db_connect("api_sessions");

/*username and password*/
$username="yiy045";
$password="...";

$login="username=$username&password=$password";

$ch=curl_init('.../api/create_session');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $login);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array (
'content-type: application/x-www-form-urlencoded',
'content-length: '. strlen($login))
);

$time_start=microtime(true);
$result= curl_exec($ch);
$time_end=microtime(true);
$execution_time = ($time_end - $time_start)/60;
curl_close($ch);
$cinfo=json_decode($result,true);

/*check if session was created*/
if($cinfo[0] == "Status: OK" && $cinfo[1] == "MSG: Session Created") {
	$sid = $cinfo[2];
	echo "Session Created!\r\n";
	echo "SID: $sid\r\n";
	/*inserts sid and open status to Database*/
	$sql = "INSERT INTO `sessions` (`session_id`,`status`) VALUES ('$sid','open')";
	$dblink->query($sql) or
		die("Something went wrong with $sql<br>".$dblnk->error);
}

/*if something went wrong send error to error_log*/
else {
	echo "$cinfo[0]";
	echo "\r\n";
	echo "$cinfo[1]";
	echo "\r\n";
	echo "$cinfo[2]";
	echo "\r\n";
	
	/*sends error to db*/
	$sql = "INSERT INTO `error_logs` (`error_message`,`action`) VALUES ('$cinfo[1]','$cinfo[2]')";
	$dblink->query($sql) or
		die("Something went wrong with $sql<br>".$dblink->error);
}
?>