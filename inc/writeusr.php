<?php
  include("inc/tidy.php");

  if ( "$because" <> "" ) {
    $because = "<H3>User Not Added</H3><P>$because</P>\n";
  }
  else {
    // Actually write the usr...
    $query = "BEGIN TRANSACTION;";
    $result = pg_Exec( $wrms_db, $query );

    // Get the user number ...
    if ( "$M" == "add" ) {
      $query = "SELECT NEXTVAL( 'usr_user_no_seq' );";
      $result = pg_Exec( $wrms_db, $query );
      if ( !$result || !pg_NumRows($result)  ) {
        $query = "ABORT TRANSACTION;";
        $result = pg_Exec( $wrms_db, $query );
      }
      else {
        $user_no = pg_Result( $result, 0, 'nextval');
      }
    }

    // OK, so if we have a valid user number...
    if ( isset($user_no) && $user_no > 0 ) {
      $UserEMail    = strtolower("$UserEMail");
      $UserName     = tidy("$UserName");
      $UserFullName = tidy("$UserFullName");
      $UserPhone    = tidy("$UserPhone");
      $UserPassword = tidy("$UserPassword");
      $UserFax      = tidy("$UserFax");
      $UserPager    = tidy("$UserPager");
      if ( "$M" == "add" ) {
        $query = "INSERT INTO usr ( user_no, username, email, fullname, organisation, phone, fax, pager, ";
        $query .= " mail_style, status, last_update";
        if ( $UserPassword <> "      " ) $query .= ", password";
        $query .= ")  VALUES(";
        $query .= "$user_no, LOWER('$UserName'), LOWER('$UserEmail'), '$UserFullName', '$UserOrganisation', ";
        $query .= " '$UserPhone', '$UserFax', '$UserPager', '$UserMail', '$UserStatus', 'now' ";
        if ( $UserPassword <> "      " ) $query .= ", '$UserPassword' ";
	$query .= " ) ";
      }
      else {
        $query = "UPDATE usr SET email=LOWER('$UserEmail'), fullname='$UserFullName', ";
        if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
          if ( "$UserOrganisation" <> "" ) $query .= " org_code='$UserOrganisation', ";
        }
        else
          $query .= " organisation='$session->org_code', ";
        $query .= " phone='$UserPhone', fax='$UserFax', ";
        $query .= " pager='$UserPager', ";
        if ( "$UserName" <> "" ) $query .= " username=LOWER('$UserName'), ";
        $query .= " mail_style='$UserMail', status='$UserStatus', last_update='now'";
        if ( $UserPassword <> "      " ) $query .= ", password='$UserPassword'";
        $query .= " WHERE user_no='$user_no' ";
      }
      $result = pg_Exec( $wrms_db, $query );
      if ( ! $result ) $because .= "<p>$query</p>";

      $query = "COMMIT TRANSACTION;";
      $result = pg_Exec( $wrms_db, $query );
      $because .= "<H3>User Record Written for $UserFullName</H3>\n";

      // Roles
      if ( isset($NewUserRole) && is_array($NewUserRole) ) {
        $query = "DELETE FROM group_member WHERE user_no=$user_no;";
        $result = pg_Exec( $wrms_db, $query );
        if ( ! $result ) $because .= "<p>$query</p>";
        while ( is_array($NewUserRole) && list($k1, $val) = each($NewUserRole)) {
//          echo "<p>Roles: $k1, $val</p>";
          if ( is_array($val) ) {
            /* This should be caught by PHP4 */
            while ( list($k2, $val2) = each($val) ) {
              $query = "INSERT INTO group_member (user_no, group_no) SELECT $user_no AS user_no, group_no FROM ugroup";
              $query .= " WHERE module_name='$k1' ";
              $query .= " AND group_name='$k2'; ";
              $result = pg_Exec( $wrms_db, $query );
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
            $result = pg_Exec( $wrms_db, $query );
            if ( ! $result ) $because .= "<p>$query</p>";
          }
        }
        reset($UserRole);
      }

       // Write allowed systems
      if ( isset($NewUserCat) && is_array($NewUserCat) ) {
        $query = "DELETE FROM system_usr WHERE user_no=$user_no";
        $result = pg_Exec( $wrms_db, $query );
        while ( list($k1, $val) = each($NewUserCat)) {
          if ( "$val" == "" ) continue;
          $query = "INSERT INTO system_usr (user_no, system_code, role) ";
          $query .= " VALUES( $user_no, '$k1', '$val') ";
          $result = pg_Exec( $wrms_db, $query );
          if ( ! $result ) $because .= "<p>$query</p>";
        }
        reset($NewUserCat);
      }
    }  // valid user no
  }  // validated OK

?>
