<?php
/******** File always included into application *********/

$begin_processing = microtime();
// Always connect to the database...
$wrms_db = pg_Connect("dbname=wrms user=general");
$dbconn = $wrms_db;

$admin_email = "wrmsadmin@catalyst.net.nz";
$basefont = "verdana,sans-serif";
$system_name = "Catalyst WRMS";
$sysabbr = "wrms";

$base_dns = "http://$HTTP_HOST";
$base_url = "";
$base_dir = "/var/www/wrms.catalyst.net.nz";
$module = "base";
$images = "images";
$colors = array( 
	0 => "#ccbea1", // primary background
	"bg1" => "#ccbea1", // primary background
	1 =>  "#ffffff", // secondary background (behind menus)
	"bg2" =>  "#ffffff", // secondary background (behind menus)
	2 =>  "#302080", // text on primary background
	"fg1" =>  "#302080", // text on primary background
	3 =>  "#802050", // text on secondary background
	3 =>  "#802050", // text on secondary background
	4 =>  "#5e486f", // text on links
	"fg2" =>  "#5e486f", // text on links
	5 =>  "#886c50", // tertiary background, column headings
	"bg3" =>  "#886c50", // tertiary background, column headings
	"fg3" =>  "#f0fff0", // tertiary background, column headings
	6 =>  "#e4ddc2", // dark rows in listings
	"row1" =>  "#e4ddc2", // dark rows in listings
	7 =>  "#f4f0dc", // light rows in listings
	"row2" =>  "#f4f0dc", // light rows in listings
	8 =>  "#583818", // Form headings
	8 =>  "#583818", // Form headings
	9 =>  "#ccbea1", // Mandatory forms
	9 =>  "#ccbea1", // Mandatory forms
	10 =>  "#50a070", // whatever!
	10 =>  "#50a070", // whatever!

// Parts of a text block (default colors - some blocks might code around this
		"blockfront" => "white",
		"blockback" => "#a78660",
		"blockbg2" => "#8767c0",
		"blocktitle" => "#876640",
		"blocksides" => "#ffffff",
		"blockextra" => "#bb8855"
);

$fonts = array( "tahoma",		// primary font
		"verdana",	// secondary font
		"help"	=> "times",		// help text
		"quote"	=> "times new roman, times, serif", // quotes in messages
		"fixed"	=> "courier, fixed",	// monospace font
		"block"	=> "tahoma"); 	// block font

  if ( "$L" == "" && "$E" == "" && "$M" == "LC" ) {
    // Not a login after all.
    unset($M);
    unset($L);
    unset($E);
  }

// Do some basic browser-detection here to default the font sizes
// we actually do more browser detection than (may) be needed
// because we haven't seen this on all browsers yet (if ever :-)
  if ( eregi( "MSIE.*[4]\.", $HTTP_USER_AGENT) ) {
    $fontsizes = array( "xx-small", "x-small", "small", "medium" );
    $agent = "ie4";
  }
  else if ( eregi( "MSIE.*[5678]", $HTTP_USER_AGENT) ) {
    $fontsizes = array( "xx-small", "x-small", "small", "medium" );
    $agent = "ie5";
  }
  else if ( eregi( "ozilla.4", $HTTP_USER_AGENT) ) {
    $fontsizes = array( "x-small", "small", "medium", "medium" );
    $agent = "moz4";
  }
  else if ( eregi( "ozilla.[5678]", $HTTP_USER_AGENT) ) {
    $fontsizes = array( "x-small", "small", "medium", "large" );
    $agent = "moz5";
  }
  else {
    $fontsizes = array( "xx-small", "x-small", "small", "medium" );
    $agent = "default";
  }


// Set the bebug variable initially to '0'. This variable is made available 
// to all local routines for verbose printing. 

if ( !isset($debuglevel) ) $debuglevel = 0;

class Setting {
  var $parameters;  // parameters we have set

  function Setting( $fromtext = "" ) {
    $session_data = unserialize ($fromtext);
    if (!is_array ($session_data)) {
      // something went wrong, initialize to empty array
      $session_data = array();
    }
    $this->parameters = $session_data;
  }

  function set ($key, $value) {
    $this->parameters[$key] = $value;
  }

  function get ($key) {
    return $this->parameters[$key];
  }

  function forget($key) {
    unset( $this->parameters[$key] );
  }

  function to_save() {
    return serialize( $this->parameters );
  }
}


//-----------------------------------------
// Function to tell difference between two 'microtime()'s
//-----------------------------------------
function duration( $t1, $t2 ) {
  // Return a duration, given a "mS S" string such as returned from microtime()
  list( $ms1, $s1 ) = explode( " ", $t1 );
  list( $ms2, $s2 ) = explode( " ", $t2 );
  $s1 = $s2 - $s1;
  $s1 = $s1 + ( $ms2 - $ms1 );
  return $s1;
}


///////////////////////////////////////////////////////////////////////////////////////////////
// Should be a drop-in replacement for pg_Exec($conn,$query)
// - can set global variable $sysabbr to identify system in logs
// - successful queries are timed and logged to syslog.
// - failed queries are logged and contain string "QF:"
///////////////////////////////////////////////////////////////////////////////////////////////
$total_query_time = 0.0 ;
function awm_pgexec( $myconn, $query, $location="", $abort_on_fail=false, $mydbg=0 ) {
  global $sysabbr, $debuglevel, $total_query_time, $REQUEST_URI;

  $a1 = microtime();
  $result = pg_Exec( $myconn, $query );
  $a2 = microtime();
  $locn = sprintf( "%-12.12s", $location);
  $taken = sprintf( "%2.06lf", duration( $a1, $a2 ));
  $total_query_time += $taken;
  if ( !$result && $abort_on_fail ) {
    $result = pg_Exec( $dbconnection, "ABORT;" );
    if ( $debuglevel > 4 || $mydbg > 4) error_log( "$sysabbr $locn QF-ABRT: $query", 0);
  }
  else if ( !$result )
    error_log( "$sysabbr $locn QF: $query", 0);
  else if ( $debuglevel > 4  || $mydbg > 4 ) {
    error_log( "$sysabbr $locn URI: $REQUEST_URI", 0);
    error_log( "$sysabbr $locn QT: $taken for: $query", 0);
  }
  else if ( $debuglevel > 2  || $mydbg > 2 ) {
    error_log( "$sysabbr $locn QT: $taken for: " . substr( $query, 0,200), 0);
  }
  else if ( $taken > 20 ) {
    error_log( "$sysabbr $locn SQ: $taken for: $query", 0);
  }

  return $result;
}


//-----------------------------------------
// Handle nicer date formatting.  Note global call to set
// known DATESTYLE first.
//-----------------------------------------
awm_pgexec( $dbconn, "SET DATESTYLE TO 'ISO';", "always" );
function nice_date($str) {
  return substr($str, 11, 5) . ", " . substr($str, 8, 2) . "/" . substr($str, 5, 2) . "/" . substr($str, 0, 4);
}

//-----------------------------------------
// Function to start a block of options / menus / whatever
//-----------------------------------------
function block_open(  $bgcolor="", $border_color="") {
  global $colors;
  if ( $bgcolor == "" ) $bgcolor=$colors["blockback"];
  if ( $border_color == "" ) $border_color=$colors["blocksides"];
  echo "<table cellpadding=\"1\" cellspacing=\"0\" border=\"0\" width=\"100%\" bgcolor=\"$border_color\">\n";
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
