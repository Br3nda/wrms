<?php 
	if(!isset($dbconn)) {
		$dbconn = pg_Connect("dbname=wrms user=general"); 
	}
?>
