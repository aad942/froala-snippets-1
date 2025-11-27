<?php
    $serverName = "localhost";

    $connectionInfo = array("Database"=>"testdb");
    $conn = sqlsrv_connect($serverName, $connectionInfo);

	if ($conn === false) {
		echo "Connection could not be established. \n";
		die(print_r(sqlsrv_errors(), true));
	}
?>
