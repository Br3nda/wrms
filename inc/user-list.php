<?php
function get_user_list( $role="", $org="", $current ) {
  global $wrms_db;
  global $session;
  $user_list = "";

  $query = "SELECT DISTINCT usr.user_no, usr.fullname, organisation.abbreviation ";
  $query .= "FROM usr , organisation";
  $query .= " WHERE usr.status <> 'I' ";
  $query .= " AND usr.org_code = organisation.org_code ";
  if( $role <> "" ) {
    $query .= "AND EXISTS (SELECT group_member.user_no FROM group_member, ugroup ";
    $query .= "WHERE group_member.user_no = usr.user_no ";
    $query .= "AND ugroup.group_no = group_member.group_no ";
    $query .= "AND ugroup.group_name = '$role' )";
  }
  if ( "$org" <> "" )         $query .= " AND usr.org_code='$org' ";
  $query .= " ORDER BY usr.fullname; ";

  $rid = awm_pgexec( $wrms_db, $query, "userlist", false, 7);
  if ( ! $rid ) {
    echo "<p>$query";
  }
  else if ( pg_NumRows($rid) > 0 ) {
    // Build table of users found
    $rows = pg_NumRows( $rid );
    for ( $i=0; $i < $rows; $i++ ) {
      $user = pg_Fetch_Object( $rid, $i );
      $user_list .= "<OPTION VALUE=\"$user->user_no\"";
      if ( "$user->user_no" == "$current" ) $user_list .= " SELECTED";
      $user->fullname = substr( $user->fullname, 0, 25) . " ($user->abbreviation)";
      $user_list .= ">$user->fullname";
    }
  }

  return $user_list;
}
?>