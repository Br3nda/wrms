<?php

function update_timesheet( $ts_finish ) {
  global $ts_no, $ts_start, $ts_description, $because, $dbconn, $dow, $sow, $session;

  $ts_finish = intval($ts_finish);
  if ( $ts_no > 0 && $ts_finish > 0 ) {
    error_log( "Write timesheet from $ts_start to $ts_finish for $ts_no '$ts_description'", 0);
    $query = "SELECT request_id FROM request WHERE request_id = $ts_no;";
    $result = awm_pgexec( $dbconn, $query, 'ts-action');
    if ( !$result || pg_NumRows($result) == 0 ) {
      $because .= "<p class=error>WR # $ts_no was not found</p>\n";
      error_log( "WR# $ts_no '$ts_description' was not found.", 0);
    }
    else {
      $from = "'" . date( 'Y-M-d, H:i', $sow + ($dow * 86400) + ($ts_start * 60) ) . "'::timestamp";
      $duration = sprintf( "'%d minutes'::timespan", $ts_finish - $ts_start );
      $quantity = ($ts_finish - $ts_start ) / 60;
      $description = addslashes( ereg_replace( "@\|@.*\$", "", $ts_description ) );
      if ( strpos( $ts_description, "@|@" ) ) {
        $query = "INSERT INTO request_timesheet ( request_id, work_on, work_duration, work_quantity, work_by_id, work_description, work_units, entry_details ) ";
        $query .= "VALUES( $ts_no, $from, $duration, $quantity, $session->user_no, '$description', 'hours', ";
        $query .= sprintf( "'TS-%d-%d');", $session->user_no, $sow );
      }
      else {
        $query = "INSERT INTO request_timesheet ( request_id, work_on, work_duration, work_quantity, work_by_id, work_description, work_units, entry_details ) ";
        $query .= "VALUES( $ts_no, $from, $duration, $quantity, $session->user_no, '$description', 'hours', ";
        $query .= sprintf( "'TS-%d-%d');", $session->user_no, $sow );
      }
      $result = awm_pgexec( $dbconn, $query, 'ts-action' );
    }
  }
  else {
    error_log( "Not writing timesheet from $ts_start to $ts_finish for $ts_no '$ts_description'", 0);
  }

  $ts_no = 0;
  $ts_start = 0;
  $ts_description = "";
  return;
}

  if ( $logged_on && isset( $sow) && isset($tm) && is_array( $tm ) ) {
    $sow = intval($sow);
    $query = sprintf( "DELETE FROM request_timesheet WHERE entry_details = 'TS-%d-%d';", $session->user_no, $sow );
    $result = awm_pgexec( $dbconn, $query, 'ts-action' );
    for ( $dow = 0; $dow < 7; $dow ++ ) {
//      error_log( "dow = $dow", 0);
      while( list( $k, $v ) = each ( $tm[$dow] ) ) {
//        error_log( "tm[$dow]: $k - $v", 0);
        if ( $ts_no > 0 && $v == "" ) {
          // Close off an existing timesheet
          update_timesheet( substr($k, 1) );
        }
        elseif ( $v != "" ) {
          list( $number, $description ) = split( '/', $v, 2);
          $number = intval($number);
          if ( ($number != $ts_no && $ts_no > 0) ||  ( "$description" != "$ts_description" && "$description" != "") ) update_timesheet( substr($k,1));
          if ( $number > 0 ) {
            $ts_no = $number;
            if ( $ts_start == 0 ) $ts_start = intval(substr($k,1));
            if ( "$description" != "" ) $ts_description = $description;
            if ( "$ts_description" == "" ) $ts_description = "$session->fullname - Weekly T/S";
          }
          error_log( "Setting description: $k - $v => no: $ts_no, desc: $ts_description", 0);
        }
      }
      // Update to the end of the day
      if ( $ts_no > 0 ) update_timesheet( $eod );

    }

    $query = "DELETE FROM timesheet_note ";
    $query .= "WHERE note_by_id = $session->user_no ";
    $query .= "AND note_date >= '" . date( 'Y-M-d', $sow ) . "' ";
    $query .= "AND note_date < '" . date( 'Y-M-d', $sow + (7 * 86400) ) . "' ";
    $rid = awm_pgexec( $dbconn, $query, "ts-action" );
    if ( isset($tnote) && is_array( $tnote ) ) {
      for ( $dow = 0; $dow < 7; $dow ++ ) {
        if ( trim(  $tnote[$dow] ) == "" ) continue;
        $from = "'" . date( 'Y-M-d, H:i', $sow + ($dow * 86400) ) . "'::timestamp";
        $query = "INSERT INTO timesheet_note ( note_date, note_by_id, note_detail ) ";
        $query .= "VALUES( $from, $session->user_no, '" . addslashes( trim( $tnote[$dow]) ) . "' ); ";
        $rid = awm_pgexec( $dbconn, $query, "ts-action" );
      }
    }

  }
?>