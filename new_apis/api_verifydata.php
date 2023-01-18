<?php
include("/var/www/html/functions.php");
$dblink=db_connect("api_sessions");

$username="yiy045";
$sql = "SELECT session_id FROM sessions WHERE auto_id = (SELECT MAX(auto_id) FROM sessions) AND status = 'open'";
$result=$dblink->query($sql) or
	die("Something went wrong with $sql<br>".$dblink->error);
$data=$result->fetch_array(MYSQLI_ASSOC);
$sid=$data['session_id'];

echo "Session id: \"$sid\"\r\n";

$data = "sid=$sid&uid=$username";
$ch=curl_init('.../api/request_loans');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'content-type: application/x-www-form-urlencoded', 
	'content-length: ' . strlen($data)));

$time_start = microtime(true);
$time_end = microtime(true);
$result = curl_exec($ch);
$exec_time = ($time_end - $time_start)/60;
curl_close($ch);
$cinfo=json_decode($result,true);
print_r($cinfo);
$temp = explode(":", $cinfo[1]);
$loans=json_decode($temp[1], true);

//$temp = explode(" ", $cinfo[1]);
//$temp = explode("[", $temp[1]);
//$temp = explode("]", $temp[1]);
//$temp = explode(",", $temp[0]);
//$temp = str_replace("\"", "", $temp);

print_r($loans);

$dblink=db_connect("docstorage");
$sql = "SELECT DISTINCT `accountID` from `documents`";
$result=$dblink->query($sql) or
	die("Something went wrong with $sql<br>".$dblink->error);
while($data=$result->fetch_array(MYSQLI_ASSOC)) {
	$loanIds[] = $data['accountID'];
}
$missingLoanIds = array();
$matchingIds = array();

foreach($loans as $key=>$value) {
	if(!in_array($value, $loanIds)) {
		array_push($missingLoanIds, $value);
	}
}

print(count($missingLoanIds)."\r\n");
if(count($missingLoanIds) == 0) {
	die();
}
$totalMissingFiles = array();
//print_r($missingLoanIds);
//die();
foreach($missingLoanIds as $key=>$value) {
	$data = "sid=$sid&uid=$username&lid=$value";
	//print($data);
	$ch=curl_init('.../api/request_file_by_loan');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'content-type: application/x-www-form-urlencoded', 
		'content-length: ' . strlen($data)));

	$time_start = microtime(true);
	$time_end = microtime(true);
	$result = curl_exec($ch);
	$exec_time = ($time_end - $time_start)/60;
	curl_close($ch);
	$cinfo=json_decode($result,true);
	//print_r($result);
	
	$temp_loan_files = explode(":", $cinfo[1]);
	$loan_files=json_decode($temp_loan_files[1], true);
	//print_r($cinfo);
	//die();
	if(count($loan_files) == 0) {
		die();
	}
	foreach($loan_files as $key=>$value) {
		array_push($totalMissingFiles, $value);
		//echo $value."\r\n";
	}
	
	
}
print("Total files: ".count($totalMissingFiles));
$dblink=db_connect("api_sessions");
foreach($totalMissingFiles as $key=>$value) {
	//print($value."\r\n");
	$value = "/storage/files/yiy045/".$value;
	$sql = "INSERT INTO `file_info` (`session_id`, `file_name`,`status`) VALUES ('$sid', '$value','not used')";
	$result=$dblink->query($sql) or
		die("Something went wrong with: $sql<br>".$dblink->error);
	
}

?>