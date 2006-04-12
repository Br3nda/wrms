<?php
  $invalid = false;

  // Validate that they are only maintaining their own timesheets
  if ( is_member_of('Admin','Support') ) {
    // OK, they can add time onto requests :-)
  }
  else if ( is_member_of('Contractor') ) {
    // Build an array of the request IDs the person is trying to put time against
    $request_ids = array();
    for ( $dow = 0; $dow < 7; $dow ++ ) {
      while( list( $k, $v ) = each ( $tm[$dow] ) ) {
        if ( $v != "" ) {
          list( $number, $description ) = split( '/', $v, 2);
          $number = intval($number);
          if ( $number > 0 ) {
            $request_ids[$number] = $number;
          }
        }
      }
    }
    // Select the user's system_role for each such system
    $sql = "SELECT request.request_id, system_usr.role ";
    $sql .= "FROM request LEFT OUTER JOIN system_usr ON request.system_code = system_usr.system_code AND system_usr.user_no=? ";
    $sql .= "WHERE request_id IN (" . implode( ",", $request_ids ) . ");";
    $qry = new PgQuery( $sql, $session->user_no );
    if ( $qry->Exec("TimeSheet") ) {
      if ( $qry->rows > 0 ) {
        while( $row = $qry->Fetch() ) {
          $request_ids[$row->request_id] = $row->role;
        }
        foreach( $request_ids AS $r_id => $role ) {
          if ( $role == $r_id ) {
            $client_messages[] = "W/R $r_id does not exist.";
            $invalid = true;
          }
          else if ( $role == "" ) {
            $client_messages[] = "You may not assign time to W/R $r_id.";
            $invalid = true;
          }
        }
      }
    }
  }
  else {
    $client_messages[] = "You may not maintain timesheet information.";
    $invalid = true;
  }

  if ( $invalid ) {
    $client_messages[] = 'Please correct and resubmit: <span style="font-size:150%;"> &nbsp; No data has been saved!</span>';
  }
  else {
    $session->Dbg("TimeSheet","Timesheet entry validated OK");
  }
?>