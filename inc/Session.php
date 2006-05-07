<?php
/**
* Session handling class and associated functions
*
* This subpackage provides some functions that are useful around web
* application session management.
*
* The class is intended to be as lightweight as possible while holding
* all session data in the database:
*  - Session hash is not predictable.
*  - No clear text information is held in cookies.
*  - Passwords are generally salted MD5 hashes, but individual users may
*    have plain text passwords set by an administrator.
*  - Temporary passwords are supported.
*  - Logout is supported
*  - "Remember me" cookies are supported, and will result in a new
*    Session for each browser session.
*
* @package   awl
* @subpackage   Session
* @author    Andrew McMillan <andrew@catalyst.net.nz>
* @copyright Catalyst IT Ltd
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2
*/

/**
* All session data is held in the database.
*/
require_once('PgQuery.php');


/**
* Make a salted MD5 string, given a string and (possibly) a salt.
*
* If no salt is supplied we will generate a random one.
*
* @param string $instr The string to be salted and MD5'd
* @param string $salt Some salt to sprinkle into the string to be MD5'd so we don't get the same PW always hashing to the same value.
* @return string The salt, a * and the MD5 of the salted string, as in SALT*SALTEDHASH
*/
function session_salted_md5( $instr, $salt = "" ) {
  global $debuggroups, $session;
  if ( $salt == "" ) $salt = substr( md5(rand(100000,999999)), 2, 8);
  $session->Dbg( "Login", "Making salted MD5: salt=$salt, instr=$instr, md5($salt$instr)=".md5($salt . $instr) );
  return ( sprintf("*%s*%s", $salt, md5($salt . $instr) ) );
}

/**
* Checks what a user entered against the actual password on their account.
* @param string $they_sent What the user entered.
* @param string $we_have What we have in the database as their password.  Which may (or may not) be a salted MD5.
* @return boolean Whether or not the users attempt matches what is already on file.
*/
function session_validate_password( $they_sent, $we_have ) {
  global $debuggroups, $session;

  $session->Dbg( "Login", "Comparing they_sent=$they_sent with $we_have" );

  // In some cases they send us a salted md5 of the password, rather
  // than the password itself (i.e. if it is in a cookie)
  $pwcompare = $we_have;
  if ( ereg('^\*(.+)\*.+$', $they_sent, $regs ) ) {
    $pwcompare = session_salted_md5( $we_have, $regs[1] );
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
    $md5_sent = session_salted_md5( $they_sent, $salt ) ;
    $session->Dbg( "Login", "Salt=$salt, comparing=$md5_sent with $pwcompare or $we_have" );
    return ( $md5_sent == $pwcompare );
  }

  // Blank passwords are bad
  if ( "" == "$we_have" || "" == "$they_sent" ) return false;

  // Otherwise they just have a plain text string, which we
  // compare directly, but case-insensitively
  return ( $they_sent == $pwcompare || strtolower($they_sent) == strtolower($we_have) );
}

/**
* Checks what a user entered against any currently valid temporary passwords on their account.
* @param string $they_sent What the user entered.
* @param int $user_no Which user is attempting to log on.
* @return boolean Whether or not the user correctly guessed a temporary password within the necessary window of opportunity.
*/
function check_temporary_passwords( $they_sent, $user_no ) {
  global $debuggroups, $session;

  if ( $GLOBALS['schema_version'] <= 1099.007 ) return false;  // Introduced at that version

  $sql = 'SELECT 1 AS ok FROM tmp_password WHERE user_no = ? AND password = ? AND valid_until > current_timestamp';
  $qry = new PgQuery( $sql, $user_no, $they_sent );
  if ( $qry->Exec('Session::check_temporary_passwords') ) {
    $session->Dbg( "Login", "Rows = $qry->rows");
    if ( $row = $qry->Fetch() ) {
      $session->Dbg( "Login", "OK = $row->ok");
      // Remove all the temporary passwords for that user...
      $sql = 'DELETE FROM tmp_password WHERE user_no = ? ';
      $qry = new PgQuery( $sql, $user_no );
      $qry->Exec('Session::check_temporary_passwords');
      return true;
    }
  }
  return false;
}

/**
* A class for creating and holding session information.
*
* @package   awl
*/
class Session
{
  /**#@+
  * @access private
  */
  var $roles;
  var $cause = '';
  /**#@-*/

  /**#@+
  * @access public
  */

  /**
  * The user_no of the logged in user.
  * @var int
  */
  var $user_no;

  /**
  * A unique id for this user's logged-in session.
  * @var int
  */
  var $session_id = 0;

  /**
  * The user's username used to log in.
  * @var int
  */
  var $username = 'guest';

  /**
  * The user's full name from their usr record.
  * @var int
  */
  var $full_name = 'Guest';

  /**
  * The user's email address from their usr record.
  * @var int
  */
  var $email = '';

  /**
  * Whether this user has actually logged in.
  * @var int
  */
  var $logged_in = false;

  /**
  * Whether the user logged in to view the current page.  Perhaps some details on the
  * login form might pollute an editable form and result in an unplanned submit.  This
  * can be used to program around such a problem.
  * @var boolean
  */
  var $just_logged_in = false;

  /**
  * The date and time that the user logged on during their last session.
  * @var string
  */
  var $last_session_start;

  /**
  * The date and time that the user requested their last page during their last
  * session.
  * @var string
  */
  var $last_session_end;
  /**#@-*/

  /**
  * Create a new Session object.
  *
  * If a session identifier is supplied, or we can find one in a cookie, we validate it
  * and consider the person logged in.  We read some useful session and user data in
  * passing as we do this.
  *
  * The session identifier contains a random value, hashed, to provide validation. This
  * could be hijacked if the traffic was sniffable so sites who are paranoid about security
  * should only do this across SSL.
  *
  * A worthwhile enhancement would be to add some degree of external configurability to
  * that read.
  *
  * @param string $sid A session identifier.
  */
  function Session( $sid="" )
  {
    global $sid, $sysname;

    $this->roles = array();
    $this->logged_in = false;
    $this->just_logged_in = false;
    $this->login_failed = false;

    if ( $sid == "" ) {
      if ( ! isset($_COOKIE['sid']) ) return;
      $sid = $_COOKIE['sid'];
    }

    list( $session_id, $session_key ) = explode( ';', $sid, 2 );

    /**
    * We regularly want to override the SQL for joining against the session record.
    * so the calling application can define a function local_session_sql() which
    * will return the SQL to join (up to and excluding the WHERE clause.  The standard
    * SQL used if this function is not defined is:
    * <code>
    * SELECT session.*, usr.* FROM session JOIN usr ON ( user_no )
    * </code>
    */
    if ( function_exists('local_session_sql') ) {
      $sql = local_session_sql();
    }
    else {
      $sql = "SELECT session.*, usr.* FROM session JOIN usr USING ( user_no )";
    }
    $sql .= " WHERE session.session_id = ? AND (md5(session.session_start::text) = ? OR session.session_key = ?) ORDER BY session.session_start DESC LIMIT 2";

    $qry = new PgQuery($sql, $session_id, $session_key, $session_key);
    if ( $qry->Exec('Session') && 1 == $qry->rows )
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
      $this->Log( "WARN: Login $this->cause" );
    }
  }


  /**
  * Utility function to log stuff with printf expansion.
  *
  * This function could be expanded to log something identifying the session, but
  * somewhat strangely this has not yet been done.
  *
  * @param string $whatever A log string
  * @param mixed $whatever... Further parameters to be replaced into the log string a la printf
  */
  function Log( $whatever )
  {
    global $c;

    $argc = func_num_args();
    $format = func_get_arg(0);
    if ( $argc == 1 || ($argc == 2 && func_get_arg(1) == "0" ) ) {
      error_log( "$c->sysabbr: $format" );
    }
    else {
      $args = array();
      for( $i=1; $i < $argc; $i++ ) {
        $args[] = func_get_arg($i);
      }
      error_log( "$c->sysabbr: " . vsprintf($format,$args) );
    }
  }

  /**
  * Utility function to log debug stuff with printf expansion, and the ability to
  * enable it selectively.
  *
  * The enabling is done by setting a variable "$debuggroups[$group] = 1"
  *
  * @param string $group The name of an arbitrary debug group.
  * @param string $whatever A log string
  * @param mixed $whatever... Further parameters to be replaced into the log string a la printf
  */
  function Dbg( $whatever )
  {
    global $debuggroups, $c;

    $argc = func_num_args();
    $dgroup = func_get_arg(0);

    if ( ! (isset($debuggroups[$dgroup]) && $debuggroups[$dgroup]) ) return;

    $format = func_get_arg(1);
    if ( $argc == 2 || ($argc == 3 && func_get_arg(2) == "0" ) ) {
      error_log( "$c->sysabbr: DBG: $dgroup: $format" );
    }
    else {
      $args = array();
      for( $i=2; $i < $argc; $i++ ) {
        $args[] = func_get_arg($i);
      }
      error_log( "$c->sysabbr: DBG: $dgroup: " . vsprintf($format,$args) );
    }
  }

  /**
  * Checks whether a user is allowed to do something.
  *
  * The check is performed to see if the user has that role.
  *
  * @param string $whatever The role we want to know if the user has.
  * @return boolean Whether or not the user has the specified role.
  */
  function AllowedTo ( $whatever ) {
    return ( $this->logged_in && isset($this->roles[$whatever]) && $this->roles[$whatever] );
  }


/**
* Internal function used to get the user's roles from the database.
*/
  function GetRoles () {
    $this->roles = array();
    $qry = new PgQuery( 'SELECT group_name AS role_name FROM group_member m join ugroup g ON g.group_no = m.group_no WHERE user_no = ? ', $this->user_no );
    if ( $qry->Exec('Session::GetRoles') && $qry->rows > 0 ) {
      while( $role = $qry->Fetch() ) {
        $this->roles[$role->role_name] = true;
      }
    }
  }


/**
* Internal function used to assign the session details to a user's new session.
* @param object $u The user+session object we (probably) read from the database.
*/
  function AssignSessionDetails( $u ) {
    // Assign each field in the selected record to the object
    foreach( $u AS $k => $v ) {
      $this->{$k} = $v;
    }

    $this->GetRoles();
    $this->logged_in = true;
  }


/**
* Attempt to perform a login action.
*
* This will validate the user's username and password.  If they are OK then a new
* session id will be created and the user will be cookied with it for subsequent
* pages.  A logged in session will be created, and the $_POST array will be cleared
* of the username, password and submit values.  submit will also be cleared from
* $_GET and $GLOBALS, just in case.
*
* @param string $username The user's login name, or at least what they entered it as.
* @param string $password The user's password, or at least what they entered it as.
* @return boolean Whether or not the user correctly guessed a temporary password within the necessary window of opportunity.
*/
  function Login( $username, $password ) {
    global $c, $debuggroups;
    $rc = false;
    $this->Dbg( "Login", "Attempting login for $username" );

    $sql = "SELECT * FROM usr WHERE lower(username) = ? ";
    $qry = new PgQuery( $sql, strtolower($username) );
    if ( $qry->Exec('Login',__LINE,__FILE__) && $qry->rows == 1 ) {
      $usr = $qry->Fetch();
      if ( session_validate_password( $password, $usr->password ) || check_temporary_passwords( $password, $usr->user_no ) ) {
        // Now get the next session ID to create one from...
        $qry = new PgQuery( "SELECT nextval('session_session_id_seq')" );
        if ( $qry->Exec('Login') && $qry->rows == 1 ) {
          $seq = $qry->Fetch();
          $session_id = $seq->nextval;
          $session_key = md5( rand(1010101,1999999999) . microtime() );  // just some random shite
          $this->Dbg( "Login", "Valid username/password for $username ($usr->user_no)" );

          // Set the last_used timestamp to match the previous login.
          $qry = new PgQuery('UPDATE usr SET last_accessed = (SELECT session_start FROM session WHERE session.user_no = ? ORDER BY session_id DESC LIMIT 1) WHERE user_no = ?;', $usr->user_no, $usr->user_no);
          $qry->Exec('Session');

          // And create a session
          $sql = "INSERT INTO session (session_id, user_no, session_key) VALUES( ?, ?, ? )";
          $qry = new PgQuery( $sql, $session_id, $usr->user_no, $session_key );
          if ( $qry->Exec('Login') ) {
            // Assign our session ID variable
            $sid = "$session_id;$session_key";

            //  Create a cookie for the sesssion
            setcookie('sid',$sid, 0,'/');
            // Recognise that we have started a session now too...
            $this->Session($sid);
            $this->Dbg( "Login", "New session $session_id started for $username ($usr->user_no)" );
            if ( isset($_POST['remember']) && intval($_POST['remember']) > 0 ) {
              $cookie .= md5( $this->user_no ) . ";";
              $cookie .= session_salted_md5($usr->user_no . $usr->username . $usr->password);
              setcookie( "lsid", $cookie, time() + (86400 * 3600), "/" );   // will expire in ten or so years
            }
            $this->just_logged_in = true;

            // Unset all of the submitted values, so we don't accidentally submit an unexpected form.
            unset($_POST['username']);
            unset($_POST['password']);
            unset($_POST['submit']);
            unset($_GET['submit']);
            unset($GLOBALS['submit']);

            if ( function_exists('local_session_sql') ) {
              $sql = local_session_sql();
            }
            else {
              $sql = "SELECT session.*, usr.* FROM session JOIN usr USING ( user_no )";
            }
            $sql .= " WHERE session.session_id = ? AND (md5(session.session_start::text) = ? OR session.session_key = ?) ORDER BY session.session_start DESC LIMIT 2";

            $qry = new PgQuery($sql, $session_id, $session_key, $session_key);
            if ( $qry->Exec('Session') && 1 == $qry->rows ) {
              $this->AssignSessionDetails( $qry->Fetch() );
            }

            $rc = true;
            return $rc;
          }
   // else ...
          $this->cause = 'ERR: Could not create new session.';
        }
        else {
          $this->cause = 'ERR: Could not increment session sequence.';
        }
      }
      else {
        $c->messages[] = 'Invalid username or password.';
        if ( $debuggroups['Login'] )
          $this->cause = 'WARN: Invalid password.';
        else
          $this->cause = 'WARN: Invalid username or password.';
      }
    }
    else {
    $c->messages[] = 'Invalid username or password.';
    if ( $debuggroups['Login'] )
      $this->cause = 'WARN: Invalid username.';
    else
      $this->cause = 'WARN: Invalid username or password.';
    }

    $this->Log( "Login failure: $this->cause" );
    $this->login_failed = true;
    $rc = false;
    return $rc;
  }



/**
* Attempts to logs in using a long-term session ID
*
* This is all horribly insecure, but its hard not to be.
*
* @param string $lsid The user's value of the lsid cookie.
* @return boolean Whether or not the user's lsid cookie got them in the door.
*/
  function LSIDLogin( $lsid ) {
    global $c, $debuggroups;
    $this->Dbg( "Login", "Attempting login for $lsid" );

    list($md5_user_no,$validation_string) = split( ';', $lsid );
    $qry = new PgQuery( "SELECT * FROM usr WHERE md5(user_no)=?;", $md5_user_no );
    if ( $qry->Exec('Login') && $qry->rows == 1 ) {
      $usr = $qry->Fetch();
      list( $x, $salt, $y) = split('\*', $validation_string);
      $my_validation = session_salted_md5($usr->user_no . $usr->username . $usr->password, $salt);
      if ( $validation_string == $my_validation ) {
        // Now get the next session ID to create one from...
        $qry = new PgQuery( "SELECT nextval('session_session_id_seq')" );
        if ( $qry->Exec('Login') && $qry->rows == 1 ) {
          $seq = $qry->Fetch();
          $session_id = $seq->nextval;
          $session_key = md5( rand(1010101,1999999999) . microtime() );  // just some random shite
          $this->Dbg( "Login", "Valid username/password for $username ($usr->user_no)" );

          // And create a session
          $sql = "INSERT INTO session (session_id, user_no, session_key) VALUES( ?, ?, ? )";
          $qry = new PgQuery( $sql, $session_id, $usr->user_no, $session_key );
          if ( $qry->Exec('Login') ) {
            // Assign our session ID variable
            $sid = "$session_id;$session_key";

            //  Create a cookie for the sesssion
            setcookie('sid',$sid, 0,'/');
            // Recognise that we have started a session now too...
            $this->Session($sid);
            $this->Dbg( "Login", "New session $session_id started for $this->username ($usr->user_no)" );
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
        $this->Dbg( "Login", "$validation_string != $my_validation ($salt - $usr->user_no, $usr->username, $usr->password)");
        $client_messages[] = 'Invalid username or password.';
        if ( $debuggroups['Login'] )
          $this->cause = 'WARN: Invalid password.';
        else
          $this->cause = 'WARN: Invalid username or password.';
      }
    }
    else {
    $client_messages[] = 'Invalid username or password.';
    if ( $debuggroups['Login'] )
      $this->cause = 'WARN: Invalid username.';
    else
      $this->cause = 'WARN: Invalid username or password.';
    }

    $this->Dbg( "Login", "$this->cause" );
    return false;
  }


/**
* Renders some HTML for a basic login panel
*
* @return string The HTML to display a login panel.
*/
  function RenderLoginPanel() {
    $action_target = htmlspecialchars(str_replace('?logout','',$_SERVER['REQUEST_URI']));
    $this->Dbg( "Login", "action_target='%s'", $action_target );
    $html = <<<EOTEXT
<div id="logon">
<form action="$action_target" method="post">
<table>
<tr>
<th class="prompt">User Name:</th>
<td class="entry">
<input class="text" type="text" name="username" size="12" /></td>
</tr>
<tr>
<th class="prompt">Password:</th>
<td class="entry">
<input class="password" type="password" name="password" size="12" />
 &nbsp;<label>forget&nbsp;me&nbsp;not: <input class="checkbox" type="checkbox" name="remember" value="1" /></label>
</td>
</tr>
<tr>
<th class="prompt">&nbsp;</th>
<td class="entry">
<input type="submit" value="GO!" alt="go" name="submit" class="submit" />
</td>
</tr>
</table>
<p>
If you have forgotten your password then: <input type="submit" value="Help! I've forgotten my password!" alt="Enter a username, if you know it, and click here." name="lostpass" class="submit" />
</p>
</form>
</div>

EOTEXT;
    return $html;
  }


/**
* Checks that this user is logged in, and presents a login screen if they aren't.
*
* The function can optionally confirm whether they are a member of one of a list
* of groups, and deny access if they are not a member of any of them.
*
* @param string $groups The list of groups that the user must be a member of one of to be allowed to proceed.
* @return boolean Whether or not the user is logged in and is a member of one of the required groups.
*/
  function LoginRequired( $groups = "" ) {
    global $c, $session;

    if ( $this->logged_in && $groups == "" ) return;
    if ( ! $this->logged_in ) {
      $c->messages[] = "You must log in to use this system.";
      include_once("page-header.php");
      if ( function_exists("local_index_not_logged_in") ) {
        local_index_not_logged_in();
      }
      else {
        echo <<<EOHTML
<h1>Log On Please</h1>
<p>For access to the $c->system_name you should log on with
the username and password that have been issued to you.</p>

<p>If you would like to request access, please e-mail $c->admin_email.</p>
EOHTML;
        echo $this->RenderLoginPanel();
      }
    }
    else {
      $valid_groups = split(",", $groups);
      foreach( $valid_groups AS $k => $v ) {
        if ( $this->AllowedTo($v) ) return;
      }
      $c->messages[] = "You are not authorised to use this function.";
      include_once("page-header.php");
    }

    include("page-footer.php");
    exit;
  }



/**
* E-mails a temporary password in response to a request from a user.
*
* This could be called from somewhere within the application that allows
* someone to set up a user and invite them.
*
* This function includes EMail.php to actually send the password.
*/
  function EmailTemporaryPassword( $username, $email_address, $body_template="" ) {
    global $c;

    $password_sent = false;
    $where = "";
    if ( isset($username) && $username != "" ) {
      $where = "WHERE status='A' AND usr.username = ". qpg($username );
    }
    else if ( isset($email_address) && $email_address != "" ) {
      $where = "WHERE status='A' AND usr.email = ". qpg($email_address );
    }

    if ( $where != "" ) {
      $tmp_passwd = "";
      for ( $i=0; $i < 8; $i++ ) {
        $tmp_passwd .= substr( "ABCDEFGHIJKLMNOPQRSTUVWXYZ+#.-=*%@0123456789abcdefghijklmnopqrstuvwxyz", rand(0,69), 1);
      }
      $sql = "SELECT * FROM usr $where";
      $qry = new PgQuery( $sql );
      $qry->Exec("Session::EmailTemporaryPassword");
      if ( $qry->rows > 0 ) {
        $sql = "BEGIN;";

        include_once("EMail.php");
        $mail = new EMail( "Access to $c->system_name" );
        $mail->SetFrom($c->admin_email );
        $usernames = "";
        while ( $row = $qry->Fetch() ) {
          $sql .= "INSERT INTO tmp_password ( user_no, password) VALUES( $row->user_no, '$tmp_passwd');";
          $mail->AddTo( "$row->fullname <$row->email>" );
          $usernames .= "        $row->username\n";
        }
        if ( $mail->To != "" ) {
          $sql .= "COMMIT;";
          $qry = new PgQuery( $sql );
          $qry->Exec("Session::SendTemporaryPassword");
          if ( !isset($body_template) || $body_template == "" ) {
            $body_template = <<<EOTEXT
A temporary password has been requested for @@system_name@@.

Temporary Password: @@password@@

This has been applied to the following usernames:

@@usernames@@
and will be valid for 24 hours.

If you have any problems, please contact the system administrator.

EOTEXT;
          }
          $body = str_replace( '@@system_name@@', $c->system_name, $body_template);
          $body = str_replace( '@@password@@', $tmp_passwd, $body);
          $body = str_replace( '@@usernames@@', $usernames, $body);
          $mail->SetBody($body);
          $mail->Send();
          $password_sent = true;
        }
      }
    }
    return $password_sent;
  }


/**
* Sends a temporary password in response to a request from a user.
*
* This is probably only going to be called from somewhere internal.  An external
* caller will probably just want the e-mail, without the HTML that this displays.
*
*/
  function SendTemporaryPassword( ) {
    global $c;

    $password_sent = $this->EmailTemporaryPassword( $_POST['username'], $_POST['email_address'] );

    if ( ! $password_sent && ((isset($_POST['username']) && $_POST['username'] != "" )
                              || (isset($_POST['email_address']) && $_POST['email_address'] != "" )) ) {
      // Username or EMail were non-null, but we didn't find that user.

      $page_content = <<<EOTEXT
<div id="logon">
<h1>Unable to Reset Password</h1>
<p>We were unable to reset your password at this time.  Please contact
<a href="mailto:$c->admin_email">$c->admin_email</a>
to arrange for an administrator to reset your password.</p>
<p>Thank you.</p>
</div>
EOTEXT;
    }

    if ( $password_sent ) {
      $page_content = <<<EOTEXT
<div id="logon">
<h1>Temporary Password Sent</h1>
<p>A temporary password has been e-mailed to you.  This password
will be valid for 24 hours and you will be required to change
your password after logging in.</p>
<p><a href="/">Click here to return to the login page.</a></p>
</div>
EOTEXT;
    }
    else {
      $page_content = <<<EOTEXT
<div id="logon">
<h1>Temporary Password</h1>
<form action="$action_target" method="post">
<table>
<tr>
<th class="prompt" style="white-space: nowrap;">Enter your User Name:</th>
<td class="entry"><input class="text" type="text" name="username" size="12" /></td>
</tr>
<tr>
<th class="prompt" style="white-space: nowrap;">Or your EMail Address:</th>
<td class="entry"><input class="text" type="text" name="email_address" size="50" /></td>
</tr>
<tr>
<th class="prompt" style="white-space: nowrap;">and click on -></th>
<td class="entry">
<input class="submit" type="submit" value="Send me a temporary password" alt="Enter a username, or e-mail address, and click here." name="lostpass" />
</td>
</tr>
</table>
<p>Note: If you have multiple accounts with the same e-mail address, they will <em>all</em>
be assigned a new temporary password, but only the one(s) that you use that temporary password
on will have the existing password invalidated.</p>
<p>Any temporary password will only be valid for 24 hours.</p>
</form>
</div>
EOTEXT;
    }
    include_once("page-header.php");
    echo $page_content;
    include_once("page-footer.php");
    exit(0);
  }

  function _CheckLogout() {
    if ( isset($_GET['logout']) ) {
      error_log("$sysname: Session: DBG: Logging out");
      setcookie( 'sid', '', 0,'/');
      unset($_COOKIE['sid']);
      unset($GLOBALS['sid']);
      unset($_COOKIE['lsid']); // Allow a cookied person to be un-logged-in for one page view.
      unset($GLOBALS['lsid']);

      if ( isset($_GET['forget']) ) setcookie( 'lsid', '', 0,'/');
    }
  }

  function _CheckLogin() {
    global $debuggroups;
    if ( isset($_POST['lostpass']) ) {
      $this->Dbg( "Login", "User '$_POST[username]' has lost the password." );

      $this->SendTemporaryPassword();
    }
    else if ( isset($_POST['username']) && isset($_POST['password']) ) {
      $_username = $_POST['username'];
      // Try and log in if we have a username and password
      $this->Login( $_POST['username'], $_POST['password'] );
      $this->Dbg( "Login", "User %s(%s) - %s (%d) login status is %d", $_POST['username'], $_username, $this->fullname, $this->user_no, $this->logged_in );
    }
    else if ( !isset($_COOKIE['sid']) && isset($_COOKIE['lsid']) && $_COOKIE['lsid'] != "" ) {
      // Validate long-term session details
      $this->LSIDLogin( $_COOKIE['lsid'] );
      $this->Dbg( "Login", "User $this->username - $this->fullname ($this->user_no) login status is $this->logged_in" );
    }
  }


  /**
  * Function to reformat an ISO date to something nicer and possibly more localised
  * @param string $indate The ISO date to be formatted.
  * @param string $type If 'timestamp' then the time will also be shown.
  * @return string The nicely formatted date.
  */
  function FormattedDate( $indate, $type=date ) {
    $out = "";
    if ( preg_match( '#^\s*$#', $indate ) ) {
      // Looks like it's empty - just return empty
      return $indate;
    }
    if ( preg_match( '#^\d{1,2}[/-]\d{1,2}[/-]\d{2,4}#', $indate ) ) {
      // Looks like it's nice already - don't screw with it!
      return $indate;
    }
    $yr = substr($indate,0,4);
    $mo = substr($indate,5,2);
    $dy = substr($indate,8,2);
    switch ( $this->date_format_type ) {
      case 'U':
        $out = sprintf( "%d/%d/%d", $mo, $dy, $yr );
        break;
      case 'J':
        $out = sprintf( "%d/%d/%d", $yr, $mo, $dy );
        break;
      case 'E':
        $out = sprintf( "%d/%d/%d", $dy, $mo, $yr );
        break;
      default:
        $out = sprintf( "%d/%d/%d", $dy, $mo, $yr );
        break;
    }
    if ( $type == 'timestamp' ) {
      $out .= substr($indate,10,6);
    }
    return $out;
  }

}


/**
* @global resource $session
* @name $session
* The session object is global.
*/

if ( !isset($session) ) {
  Session::_CheckLogout();
  $session = new Session();
  $session->_CheckLogin();
}

?>
