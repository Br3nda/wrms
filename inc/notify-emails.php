<?php
/* find everyone who should be emailed for a change to a work request */
function notify_emails( $dbid, $req_id ) {

  if ( "$req_id" == "" ) return "";

  $query = "SELECT email FROM usr ";
  $query .= "WHERE (request_interested.user_no = usr.user_no AND request_interested.request_id = $req_id) ";
  $query .= " OR (request_allocated.allocated_to_id = usr.user_no AND request_allocated.request_id = $req_id) ";

  $peopleq = pg_Exec( $dbid, $query);

  if ( $peopleq ) {
    $rows = pg_NumRows($peopleq);
    for ( $i=0; $i < $rows; $i++ ) {
      $interested = pg_Fetch_Object( $peopleq, $i );
      if ( $i > 0 ) $to .= ", ";
      $to .= "$interested->email";
    }
  }

  return $to;
}

