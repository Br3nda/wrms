<?php 
	if(!isset($wrms_db)) {
		$wrms_db = pg_Connect("dbname=wrms user=general"); 
	}
?>
