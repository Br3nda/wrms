<?php
$begin_processing = microtime();
$error_loc = "";
$error_msg = "";
$warn_msg = "";
$client_messages = array();

error_log( "=============================================== Start $PHP_SELF" );
require_once("../config/config.php");
require_once("PgQuery.php");
require_once("html-format.php");

$schema_version = 0;
$qry = new PgQuery( "SELECT schema_major, schema_minor, schema_patch FROM wrms_revision ORDER BY schema_id DESC LIMIT 1;" );
if ( $qry->Exec("always") && $row = $qry->Fetch() ) {
  $schema_version = floatval( sprintf( "%d%03d.%03d", $row->schema_major, $row->schema_minor, $row->schema_patch) );
}

$left_panel = true;
$right_panel = false;
$hurl = "";
if ( !isset($request_id) ) $request_id= 0;
if ( !isset($style) ) $style = "";
$request_id = intval(preg_replace('/[^0-9]/', '//', $request_id));
$help_url = "/help.php?h=". str_replace(".php","",$PHP_SELF);

  if ( (! isset($L) || "$L" == "") && (!isset($E) || "$E" == "") && (! isset($M) || "$M" == "LC" ) ) {
    // Not a login after all.
    unset($M);
    unset($L);
    unset($E);
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
// Handle nicer date formatting.  Note global call to set
// known DATESTYLE first.
//-----------------------------------------
$qry = new PgQuery( "SET DATESTYLE TO 'ISO,European';" ); $qry->Exec("always");
function nice_date($str) {
  $str = trim($str);
  if ( $str == "" ) return "";
  // HH:MM, D/M/CCYY through D/M/YY and most of the variations in between
  if ( preg_match('#^([[:digit:]]{1,2}:[[:digit:]]{2},? ?)?[[:digit:]]{1,2}/[[:digit:]]{1,2}/[[:digit:]]{2,4}#', $str) ) return $str;
  $time = trim(substr($str, 11, 5));
  if ( $time != "" ) $time .= ", ";
  $date = $time . substr($str, 8, 2) . "/" . substr($str, 5, 2) . "/" . substr($str, 0, 4);
  return $date;
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
function link_writeups( $instr, $prefix = "" ) {
  global $logged_on, $current_node;
  $last_node = "";
  if ( isset($current_node) ) $last_node = "&last=$current_node";
  if ( !$logged_on ) return $instr;
//  $instr = ereg_replace("\[mailto:([^]|]+)\]", "<a class=wu href=\"mailto:\\1\">\\1</a>", $instr);
//  $instr = ereg_replace("\[(https?:[^]|]+)\]", "<a class=wu href=\"\\1\" target=\"_new\">\\1</a>", $instr);
  $instr = preg_replace("#\[$prefix([^]|]+)\|([^]|]+)\]#i", "<a class=\"wu\" href=\"/wu.php?wu=\$1$last_node\">\$2</a>", $instr);
  $instr = preg_replace("#\[$prefix([^]|]+)\]#i", "<a class=\"wu\" href=\"/wu.php?wu=\$1$last_node\">\$1</a>", $instr);
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
