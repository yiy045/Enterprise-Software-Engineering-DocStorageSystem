<?php
//creates a session, queries for the files, then closes the session
$username="";
$password="";
$data="username=$username&password=$password";

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

//checking if session was created successfully
if($cinfo[0]=="Status: OK" && $cinfo[1]="MSG: Session Created") 
{
	$sid=$cinfo[2];
	$data="sid=$sid&uid=$username";
	echo "\r\nSession Created Successfully!\r\n"; //just a test to see if it works
	echo "SID: $sid\r\n";
	echo "Create Session Execution time: $execution_time\r\n";
	
	$ch=curl_init('https://cs4743.professorvaladez.com/api/query_files');
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
	
	//checking to see if we need to query the files now
	if($cinfo[0]=="Status: OK")
	{
		//if there isn't
		if($cinfo[1]=="Action: None") 
		{
			echo "\r\nNo new files to import found\r\n";
			echo "SID: $sid\r\n";
			echo "Username: $username\r\n";
			echo "Query Files Execution Time: $execution_time";
		}
		//if there is, list them out
		else 
		{
			$tmp=explode(":",$cinfo[1]);
			$files=explode(",", $tmp[1]);
			echo "Number of files to import found: ".count($files)."\r\n";
			echo "Files:\r\n";
			foreach($files as $key=>$value) 
			{
				echo $value."\r\n";
			}
			echo "Query Files Execution Time: $execution_time\r\n" ;
		}
		$data="sid=$sid";
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
		if($cinfo[0]=="Status: OK") 
		{
			echo "Session closed successfully!\r\n";
			echo "SID: $sid\r\n";
			echo "Close Session execution time: $execution_time\r\n";
		}
		else //error happened during query
		{
			echo $cinfo[0];
			echo "\r\n";
			echo $cinfo[1];
			echo "\r\n";
			echo $cinfo[2];
			echo "\r\n";
		}
	}
	else //error happened when checking if status was ok
	{
		echo $cinfo[0];
		echo "\r\n";
		echo $cinfo[1];
		echo "\r\n";
		echo $cinfo[2];
		echo "\r\n";
	}
	
}
//else 
//{
//	echo $cinfo[0];
//	echo "\r\n";
//	echo $cinfo[1];
//	echo "\r\n";
//	echo $cinfo[2];
//	echo "\r\n";
//}
?>