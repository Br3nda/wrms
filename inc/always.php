<?php
/******** File always included into application *********/

// Always connect to the database...
$wrms_db = pg_Connect("dbname=wrms user=general");

$admin_email = "wrmsadmin@catalyst.net.nz";
$basefont = "verdana,sans-serif";
$system_name = "Catalyst WRMS";

$base_dns = "http://$HTTP_HOST";
$base_url = "";
$base_dir = "/var/www/wrms.catalyst.net.nz";
$module = "base";
$colors = array( "#ccbea1", // primary background
		 "#ffffff", // secondary background (behind menus)
		 "#302080", // text on primary background
		 "#802050", // text on secondary background
		 "#5e486f", // text on links
		 "#886c50", // tertiary background, column headings
		 "#e4ddc2", // dark rows in listings
		 "#f4f0dc", // light rows in listings
		 "#583818", // Form headings
		 "#c0b090", // Mandatory forms
		 "#50a070" );

// Set the bebug variable initially to '0'. This variable is made available 
// to all local routines for verbose printing. 

$debuglevel = 0;
?>
