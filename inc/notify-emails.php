<?php
/* find everyone who should be emailed for a change to a work request */
function notify_emails( $dbid, $req_id ) {

  if ( "$req_id" == "" ) return "";

  $query = "SELECT DISTINCT email, fullname FROM usr ";
  $query .= "WHERE (request_interested.user_no = usr.user_no";
  $query .=      " AND request_interested.request_id = $req_id) ";

  $peopleq = awm_pgexec( $dbid, $query);

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

