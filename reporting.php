<?php
include("functions.php");
$page="reporting.php";

echo "<h1>Yash Kapoor Assignment 5 Reports</h1>";
/*DB LINK TO DOCSTORAGE*/
$dblink=db_connect("docstorage");
$sql="SELECT * FROM `documents`";
$result=$dblink->query($sql) or
	die("Something went wrong with: $sql<br>".$dblink->error);

/*REPORT 1 - LOAN NUMBERS*/
echo "<h2>Report 1: Total number of unique loan numbers generated with a printout (100 pts)</h2>";
$loanArray=array();
while($loanData=$result->fetch_array(MYSQLI_ASSOC)) {
	$loanArray[]=$loanData['accountID'];
	$totalFileSize += strlen($loanData['content']);
	$filesize = strlen($loanData['content']);
	$indexID = strval($loanData['accountID']);
	$avgloanSize[$indexID] += $filesize;
}
$loanUnique=array_unique($loanArray);
echo '<div>Total # of loans: '.count($loanUnique).'</div>';
foreach($loanUnique as $key=>$value)
	echo '<div>Loan number: '.$value.'</div>';

/*REPORT 2 - filesizes*/
echo "<h2>Report 2: The total size of all documents recieved from the API and the average size of all documents across all loans (100 pts)</h2>";
echo '<div>Total file size of all documents received (Bytes): '.$totalFileSize.'</div>';
$totalAverageSize = ceil($totalFileSize / count($loanArray));
echo '<div>Average file size of all documents received (Bytes): '.$totalAverageSize.'</div>';

foreach($loanUnique as $key=>$value)
{
	$sql="SELECT count('content') from `documents` where `accountID` like '%$value%'";
	$size=$dblink->query($sql) or
		die("Something went wrong with: $sql<br>".$dblink->error);
	$tmp2=$size->fetch_array(MYSQLI_NUM);
	echo '<div>Average filesize of '.$value.': '.round($avgloanSize[$value]/$tmp2[0]).' Bytes</div>';
}


/*REPORT 3 - amount of files and the average*/
echo "<h2> Report 3: The total number of documents recieved and the average number of documents across all loan numbers. Compare each loan number to the average and state if it is above or below average (100 pts)</h2>";
echo '<div>Total # of documents recieved: '.count($loanArray).'</div>';
$averageFileCount = intdiv(count($loanArray), count($loanUnique));
echo '<div>Average # of files across all loans: ~'.$averageFileCount.'</div>';
foreach($loanUnique as $key=>$value)
{
	$sql="SELECT count('name') from `documents` where `name` like '%$value%'";
	$rst=$dblink->query($sql) or
		die("Something went wrong with: $sql<br>".$dblink->error);
	$tmp=$rst->fetch_array(MYSQLI_NUM);
	
	if($tmp[0] < $averageFileCount) {
		$aboveOrBelow = 'below average';
	}
	else if($tmp[0] == $averageFileCount) {
		$aboveOrBelow = 'average';
	}
	else {
		$aboveOrBelow = 'above average';
	}
	
	echo '<div>Loan number '.$value.' has '.$tmp[0].' document(s), <b>'.$aboveOrBelow.'</b></div>';
}

echo "<h2> Report 4: A complete loan is one that has at least one of the following documents: credit, closing, title, financial, personal, internal, legal, other</h2>";
echo "<h2>A list of all loan numbers that are missing at least one of these documents and which document(s) is missing (100 pts)</h2>";
echo "<h2>A list of all loan numbers that have all documents (100 pts)</h2>";
echo "<h2>List the total number of each document received across all loan numbers (100 pts)</h2>";
/*sql command*/
$sql = "SELECT accountID,
    COUNT(*) AS total,
    SUM(case when NAME LIKE '%Credit%' then 1 ELSE 0 END) AS CreditCount,
    SUM(case when NAME LIKE '%Closing%' then 1 ELSE 0 END) AS ClosingCount,
    SUM(case when NAME LIKE '%Title%' then 1 ELSE 0 END) AS TitleCount,
    SUM(case when NAME LIKE '%Financial%' then 1 ELSE 0 END) AS FinancialCount,
    SUM(case when NAME LIKE '%Personal%' then 1 ELSE 0 END) AS PersonalCount,
    SUM(case when NAME LIKE '%Internal%'then 1 ELSE 0 END) AS InternalCount,
    SUM(case when NAME LIKE '%Legal%' then 1 ELSE 0 END) AS LegalCount,
    SUM(case when NAME LIKE '%Other%' then 1 ELSE 0 END) AS OtherCount
FROM documents
GROUP BY accountID";

$result2=$dblink->query($sql) or
	die("Something went wrong with: $sql<br>".$dblink->error);


$docTypeArray = array();
$completeLoans = 0;

while($docTypeData=$result2->fetch_array(MYSQLI_ASSOC)) {
	$docsMissing = 0;
	echo "Loan number $docTypeData[accountID]<br>";
	foreach($docTypeData as $key=>$value) {
		if($key !== "accountID" && $key !== "total" && $value != 0) {
			$docTypeArray[$key] += $value;
		}
		if($value == 0) {
			$docsMissing++;
			$docType = preg_split('/(?=[A-Z])/', strval($key));
			echo "- is missing document type ".$docType[1]."<br>";
		}
	}
	if($docsMissing == 0) {
		$completeLoans++;
		echo "- recieved all document types.<br>";
	}
	echo "<br>";
	
}

foreach($docTypeArray as $key=>$value) {
	$docType = preg_split('/(?=[A-Z])/', strval($key));
	echo '<div>Total amount of '.$docType[1].' documents is '.$value.'</div>';
}

echo '<h1>NOTES</h2>';
echo '<div>- did not start querying files until Nov 15, skipped Nov 16 for last minute adjustments</div>';
echo '<div>- I realized I had a typo in my cronjob for api_fetch_files.php on the 36 minute mark every hour,
which I did not realize until Nov 29. Had to manually fetch them until I discovered that.</div>';
echo '<div><h4>cron jobs stopped at 10:10PM 11/30/2022</h4></div>';

?>