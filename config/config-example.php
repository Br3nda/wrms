<?php

$dbconn = pg_Connect("dbname=example_wrms user=general");

$admin_email = "wrmsadmin@catalyst.net.nz";
$system_name = "Example WRMS";

// To identify our logging lines 
$sysabbr = "example";

// Only admin/support can see the ranking report.
$rank_report_anyone = 0;

// is the Quality System component enabled 
$qams_enabled = false;

// Should all e-mail be sent to a debugging address 
// $debug_email = 'andrew@catalyst.net.nz';

// When searching, what are the default statuses to find 
$default_search_statuses = '@NRILKTQADSPZU';

// //////////////////// Enable for debugging...
// $debuglevel = 5;
// $debuggroups['Session'] = 1;
// $debuggroups['Login'] = 1;
// $debuggroups['querystring'] = 1;
// $debuggroups['Request'] = 1;
// $debuggroups['WorkSystem'] = 1;
// $debuggroups['TimeSheet'] = 1;

$base_dns = "http://$HTTP_HOST";
$base_url = "";
$external_base_url = $base_dns;
$base_dir = $DOCUMENT_ROOT;

// The directory where attachments are stored.
// This should be created with mode 1777 as a 'temp' directory
$attachment_dir = "/home/wrms/wrms/html/attachments";
$module = "base";

$images = "images";

$stylesheet = "main.css";

$colors = array(
	"bg1" => "#ffffff", // primary background
	"fg1" =>  "#000000", // text on primary background
	"link1" =>  "#660000", // Links on row0/row1
	"bg2" =>  "#660000", // secondary background (behind menus)
	"fg2" =>  "#ffffff", // text on links
	"bg3" =>  "#000000", // tertiary background
	"fg3" =>  "#ffffff", // tertiary foreground
	"hv1" =>  "#660000", // text on hover
	"hv2" =>  "#f0ff4c", // other text on hover
	"row0" =>  "#ffffff", // dark rows in listings
	"row1" =>  "#f0ece8", // light rows in listings
	"link2" =>  "#333333", // Links on row0/row1
	"bghelp" => "#ffffff", // help background
	"fghelp" =>  "#000000", // text on help background
	8 =>  "#583818", // Form headings
	9 =>  "#ccbea1", // Mandatory forms
	9 =>  "#ccbea1", // Mandatory forms
	10 =>  "#50a070", // whatever!
	10 =>  "#50a070", // whatever!

// Parts of a text block (default colors - some blocks might code around this
		"blockfront" => "black",
		"blockback" => "white",
		"blockbg2" => "white",
		"blocktitle" => "white",
		"blocksides" => "#ffffff",
		"blockextra" => "#660000"
);

$basefont = "verdana,sans-serif";
$fonts = array( "tahoma",		// primary font
		"verdana",	// secondary font
		"help"	=> "times",		// help text
		"quote"	=> "times new roman, times, serif", // quotes in messages
		"narrow"	=> "arial narrow, helvetica narrow, times new roman, times", // quotes in messages
		"fixed"	=> "courier, fixed",	// monospace font
		"block"	=> "tahoma"); 	// block font


// Set the bebug variable initially to '0'. This variable is made available 
// to all local routines for verbose printing. 
if ( !isset($debuglevel) ) $debuglevel = 2;



function block_open(  $bgcolor="", $border_color="") {
  global $colors;
  if ( $bgcolor == "" ) $bgcolor=$colors["blockback"];
  if ( $border_color == "" ) $border_color=$colors["blocksides"];
  echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"0\" width=\"100%\" bgcolor=\"$border_color\" style=\" margin: 0 1px;\">\n";
  echo "<tr><td>\n";
  echo "<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" bgcolor=\"$bgcolor\">\n";
}

//-----------------------------------------
// Function to title a block of options / menus / whatever
//-----------------------------------------
function block_title( $title="&nbsp;", $bgcolor="", $border_color="") {
  global $colors;
  if ( $bgcolor == "" ) $bgcolor=$colors["blocktitle"];
  if ( $border_color == "" ) $border_color=$colors["blocksides"];

  echo "<tr bgcolor=\"$bgcolor\">\n";
  echo "<td class=blockhead align=center>$title</td>\n";
  echo "</tr>\n";

}

//-----------------------------------------
// Function to finish a block of options / menus / whatever
//-----------------------------------------
function block_close() {
  echo "</table>\n";
  echo "</td></tr>\n";
  echo "</table>\n";
}


?>
