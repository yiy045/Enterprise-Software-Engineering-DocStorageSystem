<?php
function db_connect($db)
{
	$hostname="";
    $username="";
    $password="";
    //$db="docstorage";
//	echo "using db_connect\r\n";
//	echo "hostname: $hostname\r\n";
//	echo "username: $username\r\n";
//	echo "password: $password\r\n";
    $dblink=new mysqli($hostname,$username,$password,$db);
    if (mysqli_connect_errno())
    {
        die("Error connecting to database: ".mysqli_connect_error());   
    }
	return $dblink;
}

function redirect ( $uri )
{ ?>
	<script type="text/javascript">
	<!--
	document.location.href="<?php echo $uri; ?>";
	-->
	</script>
<?php die;
}
?>