<?php
function get_user_list( $status="", $org="", $current ) {
  global $wrms_db;
  global $session;
  $user_list = "";

  $query = "SELECT DISTINCT ON user_no usr.user_no, usr.fullname ";
  $query .= "FROM usr ";
  if ( $org <> "" )           $query .= ", organisation";
  if ( "$status$org" <> "" )  $query .= " WHERE ";
  if ( "$status" <> "" )      $query .= " usr.status~*'[$status]' ";
  if ( "$status" <> "" && "$org" <> "" )  $query .= " AND ";
  if ( "$org" <> "" )         $query .= " usr.org_code='$org' ";
  $query .= " ORDER BY usr.fullname";

  $rid = pg_Exec( $wrms_db, $query);
  if ( ! $rid ) {
    echo "<p>$query";
  }
  else if ( pg_NumRows($rid) > 1 ) {
    // Build table of users found
    $rows = pg_NumRows( $rid );
    for ( $i=0; $i < $rows; $i++ ) {
      $user = pg_Fetch_Object( $rid, $i );
      $user_list .= "<OPTION VALUE=\"$user->user_no\"";
      if ( "$user->user_no" == "$current" ) $user_list .= " SELECTED";
      $user->fullname = substr( $user->fullname, 0, 35);
      $user_list .= ">$user->fullname";
    }
  }

  return $user_list;
}

