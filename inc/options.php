<?php
  if ( !isset($maxresults) ) $maxresults = 100;
  $maxresults = intval($maxresults);

  if ( isset($session) ) return;


  error_log( "$system_name: options: WARN: Validating with options.php, not updated to Session basis." );

if ( ! function_exists('salted_md5') ) {
function salted_md5( $instr, $salt = "" ) {
  if ( $salt == "" ) $salt = substr( md5(rand(100000,999999)), 2, 8);
  return ( "*$salt*" . md5($salt . $instr) );
}
}

if ( ! function_exists('validate_password') ) {
function validate_password( $they_sent, $we_have ) {
  global $system_name;

  // In some cases they send us a salted md5 of the password, rather
  // than the password itself (i.e. if it is in a cookie)
  $pwcompare = $we_have;
  if ( ereg('^\*(.+)\*.+$', $they_sent, $regs ) ) {
    $pwcompare = salted_md5( $we_have, $regs[1] );
    if ( $they_sent == $pwcompare ) return true;
  }

  // error_log( "$system_name: vpw: DBG: we_have=$we_have, pwcompare=$pwcompare, they_sent=$they_sent" );

  if ( ereg('^\*\*.+$', $we_have ) ) {
    //  The "forced" style of "**plaintext" to allow easier admin setting
    // error_log( "$system_name: vpw: DBG: comparing=**they_sent" );
    return ( "**$they_sent" == $pwcompare );
  }

  if ( ereg('^\*(.+)\*.+$', $we_have, $regs ) ) {
    // A nicely salted md5sum like "*<salt>*<salted_md5>"
    $salt = $regs[1];
    $md5_sent = salted_md5( $they_sent, $salt ) ;
    // error_log( "$system_name: vpw: DBG: Salt=$salt, comparing=$md5_sent" );
    return ( $md5_sent == $pwcompare );
  }

  // Blank passwords are bad
  if ( "" == "$we_have" || "" == "$they_sent" ) return false;

  // Otherwise they just have a plain text string, which we
  // compare directly, but case-insensitively
  return ( $they_sent == $pwcompare || strtolower($they_sent) == strtolower($we_have) );
}
}

  // Various changes happen on the basis of $M
  if ( isset($LI) && !isset($M) && !isset($session_id) ) {
    // Handle the login cookie
      list( $E, $L ) = split( ";", $LI);
      $E = strtr( "$E", "abcdefghijklmnopqrstuvwxyz", "nopqrstuvwxyzabcdefghijklm" );
      $M = "LC";
  }
  else if ( !isset($M) ) $M = "";
  if ( "$M" == "LC" ) {
    $query = "SELECT * FROM usr WHERE ";
    $query .= "username=LOWER('$E')";
    $result = awm_pgexec( $dbconn, $query );

    if ( ! $result ) {
      $error_loc = "options.php";
      $error_qry = "$query";
    }
    else if ( pg_NumRows($result) == 0 ) {
      $error_msg = "<H3>Invalid Logon</H3><P>You have used an invalid ID or password</P>";
    }
    else {
      $usr = pg_Fetch_Object($result, 0);
      if ( ! validate_password( $L, $usr->password ) ) {
        $error_msg = "<H3>Invalid Logon</H3><P>You have used an invalid ID or password</P>";
      }
      else {
        $query = "INSERT INTO session (user_no) VALUES( '$usr->user_no' )";
        $result = awm_pgexec( $dbconn, $query );
        if ( ! $result ) {
          $error_loc = "index.php";
          $error_qry = "$query";
        }
        else {
          $query = "SELECT * FROM session WHERE session.user_no='$usr->user_no' ";
          $query .= " ORDER BY session_id DESC";
          $result = awm_pgexec( $dbconn, $query );
          if ( ! $result ) {
            $error_loc = "index.php";
            $error_qry = "$query";
          }
          else {
            $session = pg_Fetch_Object($result, 0);
            $session_id = "$session->session_id " . md5($session->session_start);
            setcookie( "session_id", "$session_id", 0, "$base_url/" );
            $logged_on = true;
            if ( isset($remember) && $remember == 1 ) {
              $cookie .= strtr( $usr->username, "abcdefghijklmnopqrstuvwxyz", "nopqrstuvwxyzabcdefghijklm" ) . ";";
              $cookie .= salted_md5($usr->password);
              setcookie( "LI", $cookie, time() + (86400 * 1800), "$base_url/" );   // will expire in five or so years
            }
          }
        }
      }
    }
  }
  else if ( "$M" == "LO" ) {
    $logged_on = false;
    setcookie( "session_id", "", 0, "$base_url/" );
    if ( intval($forget) > 0 ) {
      setcookie( "LI", "", 0, "$base_url/" );
    }
  }
  else if ( "$M" == "forgot" ) {
    $query = "SELECT * FROM usr WHERE ";
    $query .= " username=LOWER('$E')";
    $result = awm_pgexec( $dbconn, $query );

    if ( ! $result ) {
      $error_loc = "index.php";
      $error_qry = "$query";
    }
    else if ( pg_NumRows($result) == 0 ) {
      $error_msg = "<H3>Invalid Logon</H3><P>User not found.</P>";
    }
    else {
      // Send them an e-mail ...
      $usr = pg_Fetch_Object($result, 0);
      $subject = "Forgotten access code";
      $msg = "This message is sent to you as requested by the $system_name system.\n\n";
      $msg .= "User Number: $usr->user_no\n";
      $msg .= "Access Code: $usr->password\n";
      $hdrs = "From: $admin_email";
      if ( strtolower( substr("$usr->email_ok", 0, 1)) == "t" ) {
        mail( "$usr->email", $subject, $msg, $hdrs );
        $warn_msg = "<H3>EMail Sent</H3><P>Your password has been sent to &quot;$usr->email&quot; via email.</P>";
        mail( "administration@debiana.net", $subject, $msg, $hdrs );
      }
      else {
        $error_msg = "<H3>Invalid EMail</H3><P>That e-mail address has been marked as invalid.  I'm";
        $error_msg .= " afraid that you will have to contact an administrator in some other way.</P>";
        mail( "administration@debiana.net", $subject, $msg . "\n\n\n... and it looks like the e-mail is invalid too :-(", $hdrs );
      }
    }
  }
  $settings = "";

  if ( "$M" <> "LO" && ("$session_id" <> "" || "$sid" <> "") ) {
    if ( "$sid" != "" )
      list( $session_test, $session_hash ) = explode( ';', $sid, 2 );
    else
      list( $session_test, $session_hash) = explode( " ", $session_id);
    $query = "SELECT * FROM organisation, session, usr WHERE session_id='$session_test' ";
    $query .= "AND session.user_no=usr.user_no AND usr.org_code = organisation.org_code; ";
    $result = awm_pgexec( $dbconn, $query, "options" );
    if ( $result && pg_NumRows($result) > 0 ) {
      $session = pg_Fetch_Object($result, 0);

      $session_check = "$session_test " . md5($session->session_start);
      if ( "$session_id" <> "$session_check" && ( "$sid" <> "" && ($session_hash != $session->session_key)) ) {
        $error_msg = "<h3>Internal Processing Error</h3><p>An internal processing error has occurred.  You will need to log on again.</p>";
        $session->logged_in = false;
      }
      else {
        $query = "UPDATE session SET session_end='now' WHERE session_id='$session_test'; ";
        $query .= "UPDATE usr SET last_accessed='now' WHERE user_no=$session->user_no; ";
        $result = awm_pgexec( $dbconn, $query );
        $session->logged_in = true;
        $logged_on = true;
        $settings = new Setting( $session->config_data );

        $query = "SELECT * FROM group_member, ugroup WHERE group_member.group_no=ugroup.group_no ";
        $query .= " AND group_member.user_no='$session->user_no'";
        $result = awm_pgexec( $dbconn, $query );
        if ( ! $result ) {
          $error_loc = "options.php";
          $error_qry = "$query";
        }
        else if ( pg_NumRows($result) > 0 ) {
          $roles = array();
          for( $i=0; $i<pg_NumRows($result); $i++) {
            $role = pg_Fetch_Object($result, $i);
            $roles["$role->module_name"]["$role->group_name"] = 1;
          }
        }
        if ( is_member_of('Admin') ) {
          $query = "SELECT lookup_code AS system_code, 'M' AS role FROM lookup_code ";
          $query .= " WHERE source_table='user' AND source_field='system_code' ";
        }
        else {
          $query = "SELECT system_code, role FROM system_usr WHERE user_no=$session->user_no ";
        }
        $result = awm_pgexec( $dbconn, $query, 'options' );
        if ( $result && pg_NumRows($result) > 0 ) {
          $system_roles = array();
          for( $i=0; $i<pg_NumRows($result); $i++) {
            $role = pg_Fetch_Object($result, $i);
            $system_roles["$role->system_code"] = $role->role;
          }
        }
      }
    }
    // fall through if session record not found in database
  }

  if ( "$session->user_no" == "" && $SCRIPT_NAME != "/index.php" && $SCRIPT_NAME != "/request.php" && $SCRIPT_NAME != "/help.php") {
    include_once("login-page.php");
    # header("Location: $base_url");  /* Redirect browser to login page */
    exit; /* Make sure that code below does not get executed when we redirect. */
  }

  if ( is_object($settings) ) {
    $bigboxrows = $settings->get('bigboxrows');
    $bigboxcols = $settings->get('bigboxcols');
  }
  if ( intval($bigboxrows) == 0 ) $bigboxrows = 10;
  if ( intval($bigboxcols) == 0 ) $bigboxcols = 60;
?>
