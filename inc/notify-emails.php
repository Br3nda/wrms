<?php
/* find everyone who should be emailed for a change to a work request */
function notify_emails( $dbid, $req_id ) {

  if ( "$req_id" == "" ) return "";

  $query = "SELECT email, fullname FROM usr, request_interested ";
  $query .= "WHERE request_interested.user_no = usr.user_no ";
  $query .=  " AND request_interested.request_id = $req_id ";
  $query .=  " AND usr.active ";
  $query .= "UNION ";
  $query .= "SELECT email, fullname FROM usr, request_allocated ";
  $query .= "WHERE request_allocated.allocated_to_id = usr.user_no ";
  $query .=  " AND request_allocated.request_id = $req_id ";
  $query .=  " AND usr.active ";

  $peopleq = awm_pgexec( $dbid, $query, "notify-eml");
  $to = "";

  if ( $peopleq ) {
    $rows = pg_NumRows($peopleq);
    for ( $i=0; $i < $rows; $i++ ) {
      $interested = pg_Fetch_Object( $peopleq, $i );
      if ( $i > 0 ) $to .= ", ";
      $to .= "$interested->fullname <$interested->email>";
    }
  }
  return $to;
}
?>