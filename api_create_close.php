<?php
//WORK ON THIS TO DO ERROR CHECKING AND STUFF FOR CREATE AND CLOSE 
$username="";
$password="";


$data="username=$username&password=$password";
//echo $data
$ch=curl_init('https://cs4743.professorvaladez.com/api/create_session');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array (
'content-type: application/x-www-form-urlencoded',
'content-length: '. strlen($data))
);
$time_start=microtime(true);
$result= curl_exec($ch);
$time_end=microtime(true);
$execution_time = ($time_end - $time_start)/60;
curl_close($ch);
$cinfo=json_decode($result,true);

//reading json to see if the status is OK and the Session is created 
if($cinfo[0]=="Status: OK" && $cinfo[1]="MSG: Session Created") 
{
	$sid=$cinfo[2];
	$data="sid=$sid&uid=$username";
	echo "\r\nSession Created Successfully!\r\n"; //just a test to see if it works
	echo "SID: $sid\r\n";
	echo "Create Session Execution time: $execution_time";
	
	$ch=curl_init('https://cs4743.professorvaladez.com/api/close_session');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array (
		'content-type: application/x-www-form-urlencoded',
		'content-length: '. strlen($data))
);
	$time_start=microtime(true);
	$result= curl_exec($ch);
	$time_end=microtime(true); 
	$execution_time = ($time_end - $time_start)/60;
	curl_close($ch);
	$cinfo=json_decode($result,true);
	
	if($cinfo[0]=="Status: OK" && $cinfo[1]="MSG: Session Closed") //change this to be better at error handling
	{
		$sid=$cinfo[2];
		$data="sid=$sid&uid=$username";
		echo "\r\nSession Closed Successfully!\r\n";
		echo "SID: $sid\r\n";
		echo "Close Session Execution time: $execution_time\r\n";
	}
}

//need an else for error handling (retries, etc.)
?>