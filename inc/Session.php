<?php
// Session handling
// - set up the session object

include_once('PgQuery.php');

if ( isset($logout) )
{
  error_log("$sysname: Session: DBG: Logging out");
  setcookie( 'sid', '', 0,'/');
  unset($sid);
}


// Enable for debugging...
$debuggroups['Session'] = 1;
$debuggroups['Login'] = 1;
$debuggroups['querystring'] = 1;

$session = new Session();

if ( isset($username) && isset($password) )
{
  // Try and log in if we have a username and password
  $session->Login( $username, $password );
  if ( $debuggroups['Login'] )
    error_log( "$system_name: vpw: DBG: User $username - $session->fullname ($session->user_no) login status is $session->logged_in" );
}

function salted_md5( $instr, $salt = "" ) {
  if ( $salt == "" ) $salt = substr( md5(rand(100000,999999)), 2, 8);
  return ( "*$salt*" . md5($salt . $instr) );
}

function validate_password( $they_sent, $we_have ) {
  global $system_name, $debuggroups;

  // In some cases they send us a salted md5 of the password, rather
  // than the password itself (i.e. if it is in a cookie)
  $pwcompare = $we_have;
  if ( ereg('^\*(.+)\*.+$', $they_sent, $regs ) ) {
    $pwcompare = salted_md5( $we_have, $regs[1] );
    if ( $they_sent == $pwcompare ) return true;
  }

  if ( ereg('^\*\*.+$', $we_have ) ) {
    //  The "forced" style of "**plaintext" to allow easier admin setting
    // error_log( "$system_name: vpw: DBG: comparing=**they_sent" );
    return ( "**$they_sent" == $pwcompare );
  }

  if ( ereg('^\*(.+)\*.+$', $we_have, $regs ) ) {
    // A nicely salted md5sum like "*<salt>*<salted_md5>"
    $salt = $regs[1];
    $md5_sent = salted_md5( $they_sent, $salt ) ;
    if ( $debuggroups['Login'] )
      error_log( "$system_name: vpw: DBG: Salt=$salt, comparing=$md5_sent with $pwcompare" );
    return ( $md5_sent == $pwcompare );
  }

  // Blank passwords are bad
  if ( "" == "$we_have" || "" == "$they_sent" ) return false;

  // Otherwise they just have a plain text string, which we
  // compare directly, but case-insensitively
  return ( $they_sent == $pwcompare || strtolower($they_sent) == strtolower($we_have) );
}

class Session
{
  var $user_no = 0;
  var $session_id = 0;
  var $name = 'guest';
  var $full_name = 'Guest';
  var $email = '';
  var $centre_id = -1;
  var $region_id = -1;
  var $centre_name = '';
  var $roles;
  var $logged_in = false;
  var $cause = '';

  function Session()
  {
    global $sid, $sysname;

    $this->roles = array();
    $this->logged_in = false;

    if ( ! isset($sid) ) return;

    list( $session_id, $session_key ) = explode( ';', $sid, 2 );

    $sql = "SELECT session.*, usr.*, organisation.*
      FROM session, usr, organisation
      WHERE usr.user_no = session.user_no
      AND session_id = ?
      AND (md5(session_start) = ? OR session_key = ?)
      AND organisation.org_code = usr.org_code
      ORDER BY session_start DESC LIMIT 1";

    $qry = new PgQuery($sql, $session_id, $session_key, $session_key);
    if ( $qry->Exec('Session') && $qry->rows == 1 )
    {
      $this->AssignSessionDetails( $qry->Fetch() );
      $qry = new PgQuery('UPDATE session SET session_end = current_timestamp WHERE session_id=?', $session_id);
      $qry->Exec('Session');
    }
    else
    {
      //  Kill the existing cookie, which appears to be bogus
      setcookie('sid', '', 0,'/');
      $this->cause = 'ERR: Other than one session record matches. ' . $qry->rows;
      error_log( "$sysname Login $this->cause" );
    }
  }


  function AllowedTo ( $whatever )
  {
    return ( $this->logged_in && isset($this->roles[$whatever]) && $this->roles[$whatever] );
  }


  function GetRoles ()
  {
    $this->roles = array();
    $qry = new PgQuery( 'SELECT group_name AS role_name FROM group_member m join ugroup g ON g.group_no = m.group_no WHERE user_no = ? ', $this->user_no );
    if ( $qry->Exec('Login') && $qry->rows > 0 )
    {
      while( $role = $qry->Fetch() )
      {
        $this->roles[$role->role_name] = true;
      }
    }
  }


  function AssignSessionDetails( $u )
  {
    $this->user_no = $u->user_no;
    $this->name = $u->name;
    $this->fullname = $u->fullname;
    $this->email = $u->email;
    $this->org_code = $u->org_code;
    $this->config_data = $u->config_data;
    $this->session_id = $u->session_id;

    // $this->roles = explode( "|", $session_stuff->roles );
    $this->GetRoles();
    $this->logged_in = true;
  }


  function Login( $username, $password )
  {
    global $sysname, $sid, $debuggroups;
    if ( $debuggroups['Login'] )
      error_log( "$sysname: Login: DBG: Attempting login for $username" );

    $sql = "SELECT * FROM usr WHERE lower(username) = ? ";
    $qry = new PgQuery( $sql, strtolower($username), md5($password), $password );
    if ( $qry->Exec('Login') && $qry->rows == 1 ) {
      $usr = $qry->Fetch();
      if ( validate_password( $password, $usr->password ) ) {
        // Now get the next session ID to create one from...
        $qry = new PgQuery( "SELECT nextval('session_session_id_seq')" );
        if ( $qry->Exec('Login') && $qry->rows == 1 ) {
          $seq = $qry->Fetch();
          $session_id = $seq->nextval;
          $session_key = md5( rand(1010101,1999999999) . microtime() );  // just some random shite
          if ( $debuggroups['Login'] )
            error_log( "$sysname: Login: DBG: Valid username/password for $username ($usr->user_no)" );

          // And create a session
          $sql = "INSERT INTO session (session_id, user_no, session_key) VALUES( ?, ?, ? )";
          $qry = new PgQuery( $sql, $session_id, $usr->user_no, $session_key );
          if ( $qry->Exec('Login') ) {
            // Assign our session ID variable
            $sid = "$session_id;$session_key";

            //  Create a cookie for the sesssion
            setcookie('sid',$sid, 0,'/');
            // Recognise that we have started a session now too...
            $this->Session();
            error_log( "$sysname: Login: INFO: New session $session_id started for $username ($usr->user_no)" );
            return true;
          }
   // else ...
          $this->cause = 'ERR: Could not create new session.';
        }
        else {
          $this->cause = 'ERR: Could not increment session sequence.';
        }
      }
      else {
        if ( $debuggroups['Login'] )
          $this->cause = 'WARN: Invalid password.';
        else
          $this->cause = 'WARN: Invalid username or password.';
      }
    }
    else {
    if ( $debuggroups['Login'] )
      $this->cause = 'WARN: Invalid username.';
    else
      $this->cause = 'WARN: Invalid username or password.';
    }

    error_log( "$sysname Login $this->cause" );
    return false;
  }
}

?>
