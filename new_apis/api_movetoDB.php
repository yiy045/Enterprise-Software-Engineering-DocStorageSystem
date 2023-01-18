<?php
/*api_movetoDB.php - will move the files from the recieved folder to the uploads folder*/
include("/var/www/html/functions.php");
/*db_connects to docstorage*/
$dblink = db_connect("docstorage");
$dberrorlink = db_connect("api_sessions");

/*gets current sessionID*/
/*get current SID*/
//$sql = "SELECT `session_id` FROM `sessions` WHERE `auto_id` = (SELECT MAX(`auto_id`) FROM `sessions`) AND `status` = 'open'";
//$result=$dblink->query($sql) or
//	die("Something went wrong with $sql<br>".$dblink->error);
//$data=$result->fetch_array(MYSQLI_ASSOC);
//$sid=$data['session_id'];

/*loop through all files in recieve_files directory*/
$dir= new DirectoryIterator(dirname("/var/www/html/recieved_files/*.pdf"));
foreach($dir as $fileinfo) {
	/*while the file is actually there*/
	if(!$fileinfo->isDot()) { 
		$fileName = $fileinfo->getFilename();
		echo "Test $fileName\r\n";
		
		/*explode for tags*/
		$temp = explode("-",$fileName);
		$temp2 = explode(".", $temp[2]);
		
		$account_num = $temp[0];
		echo "$account_num\r\n";
		$doc_type = $temp[1];
		echo "$doc_type\r\n";
		$date=$temp2[0];
		echo "$date\r\n";
		$file_type = $temp2[1];
		echo "$file_type\r\n";
		
		/*getting contents*/
		echo "Opening file...\r\n";
		
		$path = "/var/www/html/recieved_files/$fileName";
		
		$fp = fopen($path, 'r');
		
		/*error checking*/
		if($fp) {
			echo "file is open!\r\n";
		}
		else {
			echo "error opening file";
		}
		
		/*if filesize is empty*/
		if(filesize($path) == 0) {
			//echo "Skipping over $path due to empty file\r\n";
			$sql = "INSERT INTO `file_error_logs` (`session_id`, `file_name`, `error_message`)
			VALUES ('$sid', '$path', 'empty file')";
			$dberrorlink->query($sql) or
				die("Something went wrong with $sql<br>".$dberrorlink->error);
			
			continue;
		}
		
		
		$content = fread($fp, filesize("/var/www/html/recieved_files/".$fileinfo));
		fclose($fp);
		$contentClean = addslashes($content);
		
		/*inserting into databse*/
		$sql="INSERT INTO `documents` (`accountID`, `doc_type`, `name`, `upload_by`, `status`, `file_type`, `content`)
		VALUES ('$account_num', '$doc_type', '$fileName', 'chronjob', 'active', '$file_type', '$contentClean')";
		$dblink->query($sql) or
			die("Something went wrong wiith $sql<br>".$dblink->error);
		
	}
}

?>