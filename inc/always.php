<?php
$begin_processing = microtime();
$error_loc = "";
$error_msg = "";
$warn_msg = "";
$client_messages = array();

error_log( "=============================================== $PHP_SELF" );
include_once("../config/config.php");

include_once("PgQuery.php");

$left_panel = true;
$right_panel = false;
$hurl = "";
if ( !isset($request_id) ) $request_id= 0;
if ( !isset($style) ) $style = "";
$request_id = intval($request_id);


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


class Setting {
  var $parameters;  // parameters we have set
  var $modified = false;

  function Setting( $fromtext = "" ) {
    $session_data = unserialize ($fromtext);
    if (!is_array ($session_data)) {
      // something went wrong, initialize to empty array
      $session_data = array();
    }
    $this->parameters = $session_data;
    $this->modified = false;
  }

  function set ($key, $value) {
    if ( isset($this->parameters[$key]) && $this->parameters[$key] == $value ) return;
    $this->parameters[$key] = $value;
    $this->modified = true;
  }

  function get ($key) {
    if ( !isset( $this->parameters[$key] ) ) return "";
    return $this->parameters[$key];
  }

  function forget($key) {
    if ( !isset($this->parameters[$key]) ) return;
    unset( $this->parameters[$key] );
    $this->modified = true;
  }

  function to_save() {
    return str_replace( "'", "''", str_replace( "\\", "\\\\", serialize( $this->parameters ) ));
  }

  function is_modified() {
    return $this->modified;
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
    while( strlen( $query ) > 0 ) {
      error_log( "$sysabbr $locn QF-ABRT: " . substr( $query, 0, 220) , 0);
      $query = substr( "$query", 220 );
    }
  }
  else if ( !$result ) {
    while( strlen( $query ) > 0 ) {
      error_log( "$sysabbr $locn QF: $taken for: " . substr( $query, 0, 220) , 0);
      $query = substr( "$query", 220 );
    }
  }
  else if ( $debuglevel > 4  || $mydbg > 4 ) {
    if ( $debuglevel > 6  || $mydbg > 6 )
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
// Force inheirtance to 'off'
//-----------------------------------------
// awm_pgexec( $dbconn, "SET SQL_Inheritance TO OFF;", "always" );

//-----------------------------------------
// Handle nicer date formatting.  Note global call to set
// known DATESTYLE first.
//-----------------------------------------
awm_pgexec( $dbconn, "SET DATESTYLE TO 'ISO,European';", "always" );
function nice_date($str) {
  if ( trim($str) == "" ) return "";
  return substr($str, 11, 5) . ", " . substr($str, 8, 2) . "/" . substr($str, 5, 2) . "/" . substr($str, 0, 4);
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
  $last_node = "";
  if ( isset($current_node) ) $last_node = "&last=$current_node";
  if ( !$logged_on ) return $instr;
  $instr = ereg_replace("\[mailto:([^]|]+)\]", "<a class=wu href=\"mailto:\\1\">\\1</a>", $instr);
  $instr = ereg_replace("\[(https?:[^]|]+)\]", "<a class=wu href=\"\\1\" target=\"_new\">\\1</a>", $instr);
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
