<?php
  $maxresults = 100;

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
    $result = awm_pgexec( $wrms_db, $query );

    if ( ! $result ) {
      $error_loc = "inc/options.php";
      $error_qry = "$query";
    }
    else if ( pg_NumRows($result) == 0 ) {
      $error_msg = "<H3>Invalid Logon</H3><P>You have used an invalid ID or password</P>";
    }
    else {
      $usr = pg_Fetch_Object($result, 0);
      if ( strtolower("$L") <> "$usr->password" && md5(strtolower("$L")) <> "$usr->password" && "$L" <> md5(strtolower("$usr->password")) ) {
        $error_msg = "<H3>Invalid Logon</H3><P>You have used an invalid ID or password</P>";
//        $error_msg = "<P>Password: $usr->password<BR>md5hash: " . md5(strtolower($usr->password)) . "<BR>You used: $L<BR>md5hash: " . md5(strtolower($L)) . "</p>";
      }
      else {
        $query = "INSERT INTO session (user_no) VALUES( '$usr->user_no' )";
        $result = awm_pgexec( $wrms_db, $query );
        if ( ! $result ) {
          $error_loc = "index.php";
          $error_qry = "$query";
        }
        else {
          $query = "SELECT * FROM session WHERE session.user_no='$usr->user_no' ";
          $query .= " ORDER BY session_id DESC";
//          $query .= " LIMIT 1";
          $result = awm_pgexec( $wrms_db, $query );
          if ( ! $result ) {
            $error_loc = "index.php";
            $error_qry = "$query";
          }
          else {
            $session = pg_Fetch_Object($result, 0);
            $session_id = "$session->session_id " . md5($session->session_start);
            setcookie( "session_id", "$session_id", "", "$base_url/" );
            $logged_on = true;
            if ( $remember == 1 ) {
              $cookie .= strtr( $usr->username, "abcdefghijklmnopqrstuvwxyz", "nopqrstuvwxyzabcdefghijklm" ) . ";";
              $cookie .= md5($usr->password);
              setcookie( "LI", $cookie, time() + (86400 * 1800), "$base_url/" );   // will expire in five or so years
            }
          }
        }
      }
    }
  }
  else if ( "$M" == "LO" ) {
    $logged_on = false;
    setcookie( "session_id", "", "", "$base_url/" );
    if ( intval($forget) > 0 ) {
      setcookie( "LI", "", "", "$base_url/" );
    }
  }
  else if ( "$M" == "forgot" ) {
    $query = "SELECT * FROM usr WHERE ";
    $query .= " username=LOWER('$E')";
    $result = awm_pgexec( $wrms_db, $query );

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

  if ( "$M" <> "LO" && "$session_id" <> "" ) {
    list( $session_test, $session_hash) = explode( " ", $session_id);
    $query = "SELECT * FROM organisation, session, usr WHERE session_id='$session_test' ";
    $query .= "AND session.user_no=usr.user_no AND usr.org_code = organisation.org_code; ";
    $result = awm_pgexec( $wrms_db, $query, "options" );
    if ( $result && pg_NumRows($result) > 0 ) {
      $session = pg_Fetch_Object($result, 0);

      $session_check = "$session_test " . md5($session->session_start);
      if ( "$session_id" <> "$session_check" ) {
        $error_msg = "<h3>Internal Processing Error</h3><p>An internal processing error has occurred.  You will need to log on again.</p>";
      }
      else {
        $query = "UPDATE session SET session_end='now' WHERE session_id='$session_test'; ";
        $query .= "UPDATE usr SET last_accessed='now' WHERE user_no=$session->user_no; ";
        $result = awm_pgexec( $wrms_db, $query );
        $logged_on = true;
        $settings = new Setting( $session->config_data );

        $query = "SELECT * FROM group_member, ugroup WHERE group_member.group_no=ugroup.group_no ";
        $query .= " AND group_member.user_no='$session->user_no'";
        $result = awm_pgexec( $wrms_db, $query );
        if ( ! $result ) {
          $error_loc = "inc/options.php";
          $error_qry = "$query";
        }
        else if ( pg_NumRows($result) > 0 ) {
          $roles = array();
          for( $i=0; $i<pg_NumRows($result); $i++) {
            $role = pg_Fetch_Object($result, $i);
            $roles["$role->module_name"]["$role->group_name"] = 1;
          }
        }
        if ( $roles[wrms][Admin] ) {
          $query = "SELECT lookup_code AS system_code, 'M' AS role FROM lookup_code ";
          $query .= " WHERE source_table='user' AND source_field='system_code' ";
        }
        else
          $query = "SELECT system_code, role FROM system_usr WHERE user_no=$session->user_no ";
        $result = awm_pgexec( $wrms_db, $query );
        if ( ! $result ) {
          $error_loc = "inc/options.php";
          $error_qry = "$query";
        }
        else if ( pg_NumRows($result) > 0 ) {
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

  if ( "$session->user_no" == "" && $SCRIPT_NAME != "/index.php" && $SCRIPT_NAME != "/request.php") {
    header("Location: $base_url");  /* Redirect browser to login page */
    exit; /* Make sure that code below does not get executed when we redirect. */
  }


?>
