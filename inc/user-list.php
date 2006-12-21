<?php
function get_user_list( $roles="", $org="", $current ) {
  global $dbconn;
  global $session;
  $user_list = "";

  $query = "SELECT DISTINCT usr.user_no, usr.fullname, organisation.abbreviation ";
  $query .= "FROM usr , organisation";
  $query .= " WHERE usr.active ";
  $query .= " AND usr.org_code = organisation.org_code ";
  if( $roles <> "" ) {
    $role_array = split( ',', $roles );
    $in_roles = "";
    foreach ( $role_array as $v ) {
      $in_roles .= ($in_roles == "" ? "" : ",");
      $in_roles .= "'$v'";
    }
    $query .= "AND EXISTS (SELECT role_member.user_no FROM role_member JOIN roles USING(role_no) ";
    $query .= "WHERE role_member.user_no = usr.user_no ";
    $query .= "AND roles.role_name IN ($in_roles) )";
  }
  if ( "$org" <> "" )         $query .= " AND usr.org_code='$org' ";
  $query .= " ORDER BY usr.fullname; ";

  $rid = awm_pgexec( $dbconn, $query, "userlist", false, 7);
  if ( ! $rid ) {
    echo "<p>$query";
  }
  else if ( pg_NumRows($rid) > 0 ) {
    // Build table of users found
    $rows = pg_NumRows( $rid );
    for ( $i=0; $i < $rows; $i++ ) {
      $user = pg_Fetch_Object( $rid, $i );
      $user_list .= "<OPTION VALUE=\"$user->user_no\"";

      if ( (is_array($current) && in_array($user->user_no,$current,true) ) || ( "$user->user_no" == "$current" )) $user_list .= " selected=\"SELECTED\"";
      $user->fullname = substr( $user->fullname, 0, 25) . " ($user->abbreviation)";
      $user_list .= ">$user->fullname";
    }
  }

  return $user_list;
}
?>
