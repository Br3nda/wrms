<?php
/**
* @global object $c Holds the variable configuration data for the application
*/
$c = (object) 'Configuration Data';
$c->started = microtime();
$c->messages = array();

$error_loc = "";
$error_msg = "";
$warn_msg = "";
$client_messages = array();

$c->theme = "Default";
$c->stylesheet = array("wrms.css");

error_log( "=============================================== Start $PHP_SELF for $HTTP_HOST on $_SERVER[SERVER_NAME]" );
if ( file_exists("/etc/wrms/".$_SERVER['SERVER_NAME']."-conf.php") ) {
  include_once("/etc/wrms/".$_SERVER['SERVER_NAME']."-conf.php");
}
else if ( file_exists("../config/config.php") ) {
  include_once("../config/config.php");
}
else {
  include_once("wrms_configuration_missing.php");
  exit;
}
$c->sysabbr     = $sysabbr;
$c->admin_email = $admin_email;
$c->debug_email = $debug_email;
$c->system_name = $system_name;
$c->base_dns    = $base_dns;
$c->scripts[]   = "js/date-picker.js";

if ( isset($stylesheet) ) $c->stylesheets[0] = $stylesheet;

require_once("PgQuery.php");
require_once("html-format.php");
require_once("organisation-selectors-sql.php");

$c->schema_version = 0;
$qry = new PgQuery( "SELECT schema_major, schema_minor, schema_patch FROM wrms_revision ORDER BY schema_id DESC LIMIT 1;" );
if ( $qry->Exec("always") && $row = $qry->Fetch() ) {
  $c->schema_version = doubleval( sprintf( "%d%03d.%03d", $row->schema_major, $row->schema_minor, $row->schema_patch) );
  $c->schema_major = $row->schema_major;
  $c->schema_minor = $row->schema_minor;
  $c->schema_patch = $row->schema_patch;
}

$c->code_version = 0;
$changelog = false;
if ( file_exists("../debian/changelog") ) {
  $changelog = fopen( "../debian/changelog", "r" );
}
else if ( file_exists("/usr/share/doc/wrms/changelog.Debian") ) {
  $changelog = fopen( "/usr/share/doc/wrms/changelog.Debian", "r" );
}
else if ( file_exists("/usr/share/doc/wrms/changelog") ) {
  $changelog = fopen( "/usr/share/doc/wrms/changelog", "r" );
}
if ( $changelog ) {
  list( $c->code_pkgver, $c->code_major, $c->code_minor, $c->code_patch, $c->code_debian ) = fscanf($changelog, "%s (%d.%d.%d-%d)");
  $c->code_version = (($c->code_pkgver * 1000) + $c->code_major).".".$c->code_minor;
  fclose($changelog);
}

$left_panel = true;
$right_panel = false;
$hurl = "";
if ( !isset($request_id) ) $request_id= 0;
if ( !isset($style) ) $style = "";
$request_id = intval(preg_replace('/[^0-9]/', '//', $request_id));
$help_url = "/help.php?h=". str_replace(".php","",$PHP_SELF);


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

require_once("WRMSSession.php");

/**
* Given a variable that has come from the client, escape the crap out of it
* so it is safe to use.
* @param string $unclean The variable we will be fixing
* @return string The cleaned variable
*/
function clean_component_name( $unclean ) {
  global $session;
  $cleaned = strtolower($unclean);
  $cleaned = preg_replace( "/[\"!'\\\\()\[\]|\/*{}&%@~.;:?<>]/", '', $cleaned );
  $session->Dbg( "Always", "Cleaned component name from <<%s>> to <<%s>>", $unclean, $cleaned );
  return $cleaned;
}


/**
* Given a URL (presumably the current one) and a parameter, replace the value of parameter,
* extending the URL as necessary if the parameter is not already there.
* @param string $uri The URI we will be replacing parameters in.
* @param array $replacements An array of replacement pairs array( "replace_this" => "with this"
 )
* @return string The URI with the replacements done.
*/
function replace_uri_params( $uri, $replacements ) {
  global $session;

  $replaced = $uri;
  foreach( $replacements AS $param => $new_value ) {
    $rxp = preg_replace( '/([\[\]])/', '\\\\$1', $param );  // Some parameters may be arrays.
    $regex = "/([&?])($rxp)=([^&]+)/";
    $session->Dbg("Always", "Looking for [%s] to replace with [%s] regex is %s and searching [%s]", $param, $new_value, $regex, $replaced );
    if ( preg_match( $regex, $replaced ) )
      $replaced = preg_replace( $regex, "\$1$param=$new_value", $replaced);
    else
      $replaced .= "&$param=$new_value";
  }
  if ( ! preg_match( '/\?/', $replaced  ) ) {
    $replaced = preg_replace("/&(.+)$/", "?\$1", $replaced);
  }
  $replaced = str_replace("&amp;", "--AmPeRsAnD--", $replaced);
  $replaced = str_replace("&", "&amp;", $replaced);
  $replaced = str_replace("--AmPeRsAnD--", "&amp;", $replaced);
  $session->Dbg("Always", "URI <<$uri>> morphed to <<$replaced>>");
  return $replaced;
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