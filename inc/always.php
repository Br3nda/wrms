<?php
$begin_processing = microtime();

$dbconn = pg_Connect("dbname=wrms user=general");
$wrms_db = $dbconn;
include_once("inc/PgQuery.php");
awm_pgexec( $wrms_db, "SET SQL_Inheritance TO OFF;", "always" );

$admin_email = "wrmsadmin@catalyst.net.nz";
$basefont = "verdana,sans-serif";
$system_name = "Catalyst WRMS";
$sysabbr = "wrms";
$left_panel = true;
$right_panel = false;
$hurl = "";
if ( !isset($request_id) ) $request_id= 0;
if ( !isset($style) ) $style = "";
$request_id = intval($request_id);

$base_dns = "http://$HTTP_HOST";
$base_url = "";
$base_dir = $DOCUMENT_ROOT;
$module = "base";
$images = "images";
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

$fonts = array( "tahoma",		// primary font
		"verdana",	// secondary font
		"help"	=> "times",		// help text
		"quote"	=> "times new roman, times, serif", // quotes in messages
		"narrow"	=> "arial narrow, helvetica narrow, times new roman, times", // quotes in messages
		"fixed"	=> "courier, fixed",	// monospace font
		"block"	=> "tahoma"); 	// block font

  if ( (! isset($L) || "$L" == "") && (!isset($E) || "$E" == "") && (! isset($M) || "$M" == "LC" ) ) {
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

if ( !isset($debuglevel) ) $debuglevel = 5;

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
    if ( !isset( $this->parameters[$key] ) ) return "";
    return $this->parameters[$key];
  }

  function forget($key) {
    unset( $this->parameters[$key] );
  }

  function to_save() {
    return str_replace( "'", "''", str_replace( "\\", "\\\\", serialize( $this->parameters ) ));
  }
}


///////////////////////////////////////////////////////////////////////////////////////////////
// Should be a drop-in replacement for pg_Exec($conn,$query)
// - can set global variable $sysabbr to identify system in logs
// - successful queries are timed and logged to syslog.
// - failed queries are logged and contain string "QF:"
///////////////////////////////////////////////////////////////////////////////////////////////
$total_query_time = 0.0 ;
function awm_pgexec( $myconn, $query, $location="", $abort_on_fail=FALSE, $mydbg=0 ) {
  global $sysabbr, $debuglevel, $total_query_time, $REQUEST_URI;

  $a1 = microtime();
  $result = pg_Exec( $myconn, $query );
  $a2 = microtime();
  $locn = sprintf( "%-12.12s", $location);
  $taken = sprintf( "%2.06lf", duration( $a1, $a2 ));
  $total_query_time += $taken;
  if ( !$result && $abort_on_fail ) {
    $result = pg_Exec( $myconn, "ROLLBACK;" );  // $dbconnection doesn't actually exist, changed to $myconn
    error_log( "$sysabbr $locn QF-ABRT: $query", 0);
  }
  else if ( !$result ) {
    while( strlen( $query ) > 0 ) {
      error_log( "$sysabbr $locn QF: $taken for: " . substr( $query, 0, 220) , 0);
      $query = substr( "$query", 220 );
    }
  }
  else if ( $debuglevel > 4  || $mydbg > 4 ) {
    error_log( "$sysabbr $locn URI: $REQUEST_URI", 0);
    while( strlen( $query ) > 0 ) {
      error_log( "$sysabbr $locn QT: $taken for: " . substr( $query, 0, 220) , 0);
      $query = substr( "$query", 220 );
    }
  }
  else if ( $debuglevel > 2  || $mydbg > 2 ) {
    error_log( "$sysabbr $locn QT: $taken for: " . substr( $query, 0,200), 0);
  }
  else if ( $taken > 5 ) {
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

//-----------------------------------------
// Very useful function for stripping MS-isms and other things out of the code
//-----------------------------------------
function tidy( $instr ) {
  $instr = str_replace( chr(145), "'", $instr);
  $instr = str_replace( chr(146), "'", $instr);
  $instr = str_replace( chr(147), '"', $instr);
  $instr = str_replace( chr(148), '"', $instr);
  $instr = str_replace( chr(150), '&#8212;', $instr);
  $instr = str_replace( chr(169), '&copy;', $instr);
  $instr = str_replace( chr(175), '&reg;', $instr);
  $instr = str_replace( "'", "''", $instr);
  $instr = str_replace( "\\", "\\\\", $instr);
  return $instr ;
}

//-----------------------------------------
// Function used to convert the [] notation to proper html links in help write ups
//-----------------------------------------
function link_writeups( $instr ) {
  global $logged_on, $current_node;
  if ( isset($current_node) ) $last_node = "&last=$current_node";
  if ( !$logged_on ) return $instr;
  $instr = ereg_replace("\[mailto:([^]|]+)\]", "<a class=wu href=\"mailto:\\1\">\\1</a>", $instr);
  $instr = ereg_replace("\[([^]|]+)\|([^]|]+)\]", "<a class=wu href=\"/wu.php?wu=\\1$last_node\">\\2</a>", $instr);
  $instr = ereg_replace("\[([^]|]+)\]", "<a class=wu href=\"/wu.php?wu=\\1$last_node\">\\1</a>", $instr);
  return $instr;
}

function is_member_of( ) {
  global $roles;

  $argc = func_num_args();
  for( $i = 0; $i < $argc; $i++ ) {
    $arg = func_get_arg($i);
    if ( isset($roles['wrms'][$arg]) && $roles['wrms'][$arg] ) return true;
  }
  return false;
}

?>