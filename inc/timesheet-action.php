<?php

$session->Dbg( "TimeSheet", "Actioning timesheet");

function update_timesheet( $ts_finish ) {
  global $ts_no, $ts_start, $ts_description, $client_messages, $dow, $sow, $session;

  $session->Dbg( "TimeSheet", "Updating timesheet for $ts_finish");
  $ts_finish = intval($ts_finish);
  if ( $ts_no > 0 && $ts_finish > 0 ) {
    $session->Dbg( "TimeSheet", "Write timesheet from $ts_start to $ts_finish for $ts_no '$ts_description'");
    $qry = new PgQuery("SELECT request_id FROM request WHERE request_id = ?", $ts_no);
    if ( !$qry->Exec("TimeSheet") || $qry->rows == 0 ) {
      $client_messages[] = "WR # $ts_no was not found.";
      $session->Dbg( "TimeSheet", "WR# $ts_no '$ts_description' was not found.");
    }
    else {
      $lt = localtime($sow, true);
      $session->Dbg( "TimeSheet", "Time includes DST? " . $lt['tm_isdst'] );
      $from = date( 'Y-M-d, H:i', $sow + ($dow * 86400) + (($ts_start - (60 * $lt['tm_isdst'])) * 60) );
      $duration = sprintf( "%d minutes", $ts_finish - $ts_start );
      $quantity = ($ts_finish - $ts_start ) / 60;
      $description = ereg_replace( "@\|@.*\$", "", $ts_description );
      $sql = "INSERT INTO request_timesheet ( request_id, work_on, work_duration, work_quantity, work_by_id, work_description, work_units, entry_details ) ";
      $sql .= "VALUES( ?, ?::timestamp without time zone, ?::interval, ?, $session->user_no, ?, 'hours', ";
      $sql .= sprintf( "'TS-%d-%d');", $session->user_no, $sow );
      $qry = new PgQuery($sql, $ts_no, $from, $duration, $quantity, $description );
      $qry->Exec("TimeSheet");
    }
  }
  else {
    $session->Dbg( "TimeSheet", "Not writing timesheet from $ts_start to $ts_finish for $ts_no '$ts_description'");
  }

  $ts_no = 0;
  $ts_start = 0;
  $ts_description = "";
  return;
}

  if ( isset( $sow) && isset($tm) && is_array( $tm ) ) {
    $sow = intval($sow);
    $sql = sprintf( "DELETE FROM request_timesheet WHERE entry_details = 'TS-%d-%d' AND charged_details IS NULL OR charged_details = '';", $session->user_no, $sow );
    $qry = new PgQuery( $sql );
    $qry->Exec("TimeSheet");
    for ( $dow = 0; $dow < 7; $dow ++ ) {

      $session->Dbg( "TimeSheet", "Working on day $dow of week");
      foreach ( $tm[$dow]  AS $k => $v ) {

        $session->Dbg( "TimeSheet", "k=$k, v=$v");
        if ( $ts_no > 0 && $v == "" ) {
          // Close off an existing timesheet
          update_timesheet( substr($k, 1) );
        }
        elseif ( $v != "" ) {
          list( $number, $description ) = split( '/', $v, 2);
          $session->Dbg( "TimeSheet", "$v -> $ts_start" );
          $number = intval($number);
          if ( ($number != $ts_no && $ts_no > 0) ||  ( "$description" != "$ts_description" && "$description" != "") ) {
            update_timesheet( substr($k,1));
          }
          if ( $number > 0 ) {
            $ts_no = $number;
            if ( $ts_start == 0 ) $ts_start = intval(substr($k,1));
            if ( "$description" != "" ) $ts_description = $description;
            if ( "$ts_description" == "" ) $ts_description = "$session->fullname - Weekly T/S";
          }
          $session->Dbg( "TimeSheet", "Setting description: $k - $v => no: $ts_no, desc: $ts_description");
        }
      }
      // Update to the end of the day
      if ( $ts_no > 0 ) update_timesheet( $eod );

    }

    $sql = "DELETE FROM timesheet_note ";
    $sql .= "WHERE note_by_id = $session->user_no ";
    $sql .= "AND note_date >= '" . date( 'Y-M-d', $sow - 3601 ) . "' ";  // 3601 allows for DST error!
    $sql .= "AND note_date < '" . date( 'Y-M-d', $sow + (7 * 86400) ) . "' ";
    $qry = new PgQuery($sql);
    $qry->Exec("TimeSheet");

    if ( isset($tnote) && is_array( $tnote ) ) {
      $sql = "INSERT INTO timesheet_note ( note_date, note_by_id, note_detail ) VALUES( ?::timestamp, $session->user_no, ? ); ";
      for ( $dow = 0; $dow < 7; $dow ++ ) {
        if ( trim(  $tnote[$dow] ) == "" ) continue;
        $from = date( 'Y-M-d, H:i', $sow + ($dow * 86400) );
        $qry = new PgQuery($sql, $from, $tnote[$dow]);
        $qry->Exec("TimeSheet");
      }
    }

  }
?>