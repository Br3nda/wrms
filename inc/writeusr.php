<?php
  $debuglevel = 5;

  if ( "$because" <> "" ) {
    $because = "<H3>User Not Added</H3><P>$because</P>\n";
  }
  else {
    // Actually write the usr...
    $query = "BEGIN TRANSACTION;";
    $result = awm_pgexec( $wrms_db, $query, "writeusr" );

    // Get the user number ...
    if ( "$M" == "add" ) {
      $query = "SELECT NEXTVAL( 'usr_user_no_seq' );";
      $result = awm_pgexec( $wrms_db, $query, "writeusr" );
      if ( !$result || !pg_NumRows($result)  ) {
        $query = "ABORT TRANSACTION;";
        $result = awm_pgexec( $wrms_db, $query );
      }
      else {
        $user_no = pg_Result( $result, 0, 'nextval');
      }
    }

    // OK, so if we have a valid user number...
    if ( isset($user_no) && $user_no > 0 ) {
      $UserEmail    = tidy(strtolower("$UserEmail"));
      $UserName     = tidy("$UserName");
      $UserFullName = tidy("$UserFullName");
      $UserPhone    = tidy("$UserPhone");
      $UserPassword = tidy("$UserPassword");
      $UserMobile   = tidy("$UserMobile");
      $UserNotifications   = tidy("$UserNotifications");
      if ( !is_object($usr->settings)) $usr->settings = new Setting;
      $usr->settings->set('fontsize', "$UserFontsize");

//      error_log( "status=$UserStatus==" . isset($UserStatus), 0);
      $UserStatus  = ( !isset($UserStatus) || "$UserStatus" == "A" ? "A" : "I" );
      if ( ! ($roles['wrms']['Admin'] || $roles['wrms']['Support']) && $M == "add" ) {
        $UserOrganisation = $session->org_code;
      }
      if ( "$M" == "add" ) {
        $query = "INSERT INTO usr ( user_no, username, email, fullname, org_code, phone, mobile, ";
        $query .= " mail_style, status, last_update";
        if ( $UserPassword <> "      " ) $query .= ", password";
        if ( is_object ( $usr->settings ) ) $query .= ", config_data";
        $query .= ")  VALUES(";
        $query .= "$user_no, LOWER('$UserName'), '$UserEmail', '$UserFullName', '$UserOrganisation', ";
        $query .= " '$UserPhone', '$UserMobile', '$UserNotifications', '$UserStatus', 'now' ";
        if ( $UserPassword <> "      " ) $query .= ", '$UserPassword' ";
        if ( is_object ( $usr->settings ) ) $query .= ", '" . $usr->settings->to_save() . "'";
	    $query .= " ) ";
      }
      else {
        $query = "UPDATE usr SET email='$UserEmail', fullname='$UserFullName', ";
        if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
          if ( "$UserOrganisation" <> "" ) $query .= " org_code='$UserOrganisation', ";
        }
        else
          $query .= " organisation='$session->org_code', ";
        $query .= " phone='$UserPhone', mobile='$UserMobile', ";
        $query .= " mail_style='$UserNotifications', ";
        if ( "$UserName" <> "" ) $query .= " username=LOWER('$UserName'), ";
        $query .= " status='$UserStatus', last_update='now'";
        if ( $UserPassword <> "      " ) $query .= ", password='$UserPassword'";
        if ( is_object ( $usr->settings ) ) $query .= ", config_data='" . $usr->settings->to_save() . "'";
        $query .= " WHERE user_no='$user_no' ";
        if ( $session->user_no == $usr->user_no ) $settings = $usr->settings;
      }
      $result = awm_pgexec( $wrms_db, $query, "writeusr", 4 );
      if ( ! $result ) $because .= "<p>$query</p>";

      $query = "COMMIT TRANSACTION;";
      $result = awm_pgexec( $wrms_db, $query, "writeusr", 4 );
      $because .= "<H3>User Record Written for $UserFullName</H3>\n";

      // Roles
      if ( isset($NewUserRole) && is_array($NewUserRole) ) {
        $query = "DELETE FROM group_member WHERE user_no=$user_no;";
        $result = awm_pgexec( $wrms_db, $query, "writeusr" );
        if ( ! $result ) $because .= "<p>$query</p>";
        while ( is_array($NewUserRole) && list($k1, $val) = each($NewUserRole)) {
//          echo "<p>Roles: $k1, $val</p>";
          if ( is_array($val) ) {
            /* This should be caught by PHP4 */
            while ( list($k2, $val2) = each($val) ) {
              $query = "INSERT INTO group_member (user_no, group_no) SELECT $user_no AS user_no, group_no FROM ugroup";
              $query .= " WHERE module_name='$k1' ";
              $query .= " AND group_name='$k2'; ";
              $result = awm_pgexec( $wrms_db, $query );
              if ( ! $result ) $because .= "<p>$query</p>";
            }
          }
          else {
            /* This should work with PHP3 */
            list($k2, $val2) = split("\]\[", $k1) ;
//            echo "<p>Split: $k2, $val2</p>";
            $query = "INSERT INTO group_member (user_no, group_no) SELECT $user_no AS user_no, group_no FROM ugroup";
            $query .= " WHERE module_name='$k2' ";
            $query .= " AND group_name='$val2'; ";
            $result = awm_pgexec( $wrms_db, $query );
            if ( ! $result ) $because .= "<p>$query</p>";
          }
        }
        reset($NewUserRole);
      }

       // Write allowed systems
      if ( isset($NewUserCat) && is_array($NewUserCat) ) {
        $query = "DELETE FROM system_usr WHERE user_no=$user_no";
        $result = awm_pgexec( $wrms_db, $query );
        while ( list($k1, $val) = each($NewUserCat)) {
          if ( "$val" == "" ) continue;
          $query = "INSERT INTO system_usr (user_no, system_code, role) ";
          $query .= " VALUES( $user_no, '$k1', '$val') ";
          $result = awm_pgexec( $wrms_db, $query );
          if ( ! $result ) $because .= "<p>$query</p>";
        }
        reset($NewUserCat);
      }
    }  // valid user no
  }  // validated OK

?>
