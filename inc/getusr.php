<?php
  if ( isset($user_no) && $user_no > 0 ) {
    $query = "SELECT * ";
    $query .= " FROM usr ";
    $query .= " WHERE user_no='$user_no' ";
    $usr_res = awm_pgexec( $wrms_db, $query, "getusr" );
    if ( $usr_res && pg_NumRows($usr_res ) > 0 ) {
      $usr = pg_Fetch_Object( $usr_res, 0 );

      // Collect group requestships
      $query = "SELECT module_name, group_name ";
      $query .= " FROM group_member, ugroup";
      $query .= " WHERE group_member.user_no=$user_no ";
      $query .= " AND group_member.group_no=ugroup.group_no";
      $result = awm_pgexec( $wrms_db, $query, "getusr" );
      if ( $result ) {
        // Build array of group requestships
        for ( $i=0; $i < pg_NumRows($result); $i++ ) {
          $grp = pg_Fetch_Object( $result, $i );
          $UserRole[$grp->module_name][$grp->group_name] = 1;
        }
      }

      // Collect allowed actions vs system_codes
      $query = "SELECT system_code, role FROM system_usr";
      $query .= " WHERE user_no=$user_no ";
      $query .= " ORDER BY role, system_code ";
      $result = awm_pgexec( $wrms_db, $query, "getusr" );
      if ( ! $result ) {
        // Build array of user system data
        for ( $i=0; $i < pg_NumRows($result); $i++ ) {
          $sys = pg_Fetch_Object( $result, $i );
          $UserCat[$sys->system_code] = $sys->role;
        }
      }
    }
  }
?>
