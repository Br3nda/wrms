<?php
  include("inc/always.php");
  include("inc/options.php");
  include("inc/tidy.php");

  $debuglevel = 5;
  if ( "$submit" <> "") {
    include("inc/timesheet-valid.php");
    if ( "$because" == "" ) include("inc/timesheet-action.php");
  }

  $title = "$system_name - Weekly Timesheet: $session->fullname";
  $right_panel = false;
  include("inc/headers.php");

// Helper function to tidy the automatic remembrance of our numeric settings
// $settings are saved to the user record within "inc/footers.php"
function get_numeric_setting( $name, $current, $default ) {
  global $settings;

  // Return the existing value, if it's set, or use a default if settings are invalid
  if ( isset( $current ) ) return $current;
  if ( !isset($settings) ) return $default;
  if ( !is_object($settings) ) return $default;
  if ( !is_numeric( $settings->get($name) ) ) return $default;

  return $settings->get($name) ;
}

  // Get the current settings, the user's last settings, or use a default
  $period_minutes = get_numeric_setting( 'ts_delta', $period_minutes, 60 );
  $sod = get_numeric_setting( 'ts_sod', $sod, 7 * 60 );
  $eod = get_numeric_setting( 'ts_eod', $eod, 18 * 60 );

  // Save what we have for next time
  $settings->set('ts_delta', $period_minutes );
  $settings->set('ts_sod', $sod );
  $settings->set('ts_eod', $eod );

  // Construct a selection of weeks, the top one should be the current week
  // This selection we _don't_ save :-)
  $latest_date = localtime ( time() + (86400 * 14), 1);
  $latest_week = 86400 + mktime( 0, 0, 0, $latest_date['tm_mon'] + 1, $latest_date['tm_mday'], $latest_date['tm_year']) - (86400 * $latest_date['tm_wday']);

  $week_list = "<select name=sow onchange='form.submit()'>\n";
  if ( !isset($sow) ) $sow = $latest_week - (86400 * 14);
  for( $i = 0, $sow_date = $latest_week; $i < 16; $i++, $sow_date -= (86400 * 7) ) {
    $week_list .= "<option value=$sow_date" . ( $sow == $sow_date ? " selected" : "") . ">" . date( 'j M Y', $sow_date ) . "</option>\n";
  }
  $week_list .= "</select>\n";

  // Split of timesheet
  $period_list = "<select name=period_minutes onchange='form.submit()'>\n";
  $period_list .= "<option value=60" . ($period_minutes == 60 ? " selected" : "") . ">Hourly</option>\n";
  $period_list .= "<option value=30" . ($period_minutes == 30 ? " selected" : "") . ">Half Hourly</option>\n";
  $period_list .= "<option value=15" . ($period_minutes == 15 ? " selected" : "") . ">Quarter Hour</option>\n";
  $period_list .= "<option value=120" . ($period_minutes == 120 ? " selected" : "") . ">Two Hourly</option>\n";
  $period_list .= "<option value=240" . ($period_minutes == 240 ? " selected" : "") . ">Four Hourly</option>\n";
  $period_list .= "<option value=480" . ($period_minutes == 480 ? " selected" : "") . ">Eight Hourly</option>\n";
  $period_list .= "</select>\n";

// Helper function for building select lists of times, since we need From: and To:
function build_time_list( $name, $from, $current, $delta ) {
  $time_list = "<select name=$name onchange='form.submit()'>\n";
  $to = $from + 480;
  for ( $i = $from; $i <= $to; $i += $delta ) {
    $time_list .= sprintf( "<option value=%d%s>%02d:%02d</option>\n", $i, ( $i == $current ? " selected" : ""), $i / 60, $i % 60 );
  }
  $time_list .= "</select>\n";
  return $time_list;
}

  echo '<form name=control method=post action="/timesheet.php" enctype="multipart/form-data"><table width="100%" border="0" cellpadding="1" cellspacing="2">';
  echo "<tr>\n";
  echo "<th>Week Starting:</th><td>$week_list</td>\n";
  echo "<th>Periods:</th><td>$period_list</td>\n";
  echo "<th>From:</th><td>" . build_time_list('sod', 360, $sod, $period_minutes) . "</td>\n";
  echo "<th>To:</th><td>" . build_time_list('eod', 840, $eod, $period_minutes) . "</td>\n";
  echo "</tr>\n";
  echo "</table>\n</form>\n";


  // Read the timesheets from the file and build reasonable bits from them
  $ts_user = intval($session->user_no);
  $query = "SELECT *, EXTRACT( EPOCH FROM CAST ( work_on AS TIMESTAMP WITHOUT TIME ZONE ) ) AS started, ";
  $query .= "EXTRACT( EPOCH FROM CAST ( (work_on + work_duration) AS TIMESTAMP WITHOUT TIME ZONE ) ) AS finished, ";
  $query .= "EXTRACT( DOW FROM work_on ) AS dow, 0 AS offset ";
  $query .= "FROM request_timesheet WHERE work_by_id = $ts_user ";
  $query .= "AND work_on >= '" . date( 'Y-M-d', $sow ) . "' ";
  $query .= "AND work_on < '" . date( 'Y-M-d', $sow + (7 * 86400) ) . "' ";
  $query .= "ORDER BY work_on ASC; ";

  // The query above requires 7.2, so the below (including hacks with $ts->offset) works with 7.0
  // currently in production...
  $query = "SELECT *, date_part( 'epoch', work_on ) AS started, ";
  $query .= "date_part( 'epoch', (work_on + work_duration)) AS finished, ";
  $query .= "date_part( 'dow', work_on ) AS dow, ";
  $query .= "date_part( 'epoch', '1970-1-1'::timestamp) AS offset ";
  $query .= "FROM request_timesheet WHERE work_by_id = $ts_user ";
  $query .= "AND work_on >= '" . date( 'Y-M-d', $sow ) . "' ";
  $query .= "AND work_on < '" . date( 'Y-M-d', $sow + (7 * 86400) ) . "' ";
  $query .= "ORDER BY work_on ASC; ";
  $result = awm_pgexec( $dbconn, $query, 'timesheet' );
//  echo "<p>Results: " . pg_NumRows($result);
  if ( $result && pg_NumRows($result) ) {
  //  echo "<p>Results!</p>";
    for( $i = 0; $i < pg_NumRows($result); $i++ ) {
      $ts = pg_Fetch_Object( $result, $i );
      $our_dow = ($ts->dow + 6) % 7;
      $start_tod = intval( (($ts->started + $ts->offset) % 86400) / 60 );
      $finish_tod = intval( (($ts->finished + $ts->offset) % 86400) / 60 );
      $duration = $finish_tod - $start_tod;
      // echo "<p>Day $our_dow, $ts->request_id/$ts->work_description: $start_tod: $duration - $ts->work_units - " . ($ts->work_quantity * 60) . "   =$ts->started=$ts->finished=</p>";
      if ( $duration == 0 || "$ts->finished" == "" || ($start_tod + $duration) == 0 ) {
        if ( "$ts->work_units" == "hours" )  $duration = $ts->work_quantity * 60;
      }
      if ( $duration == 0 ) continue;
      // Force times within this person's day...
      if ( $start_tod < $sod ) {
        $start_tod = $sod;
        $finish_tod = $sod + $duration;
      }
      elseif ( $finish_tod > $eod ) {
        $finish_tod = $eod;
        $start_tod = $eod - $duration;
      }

      // echo "<p>$out_dow from $start_tod for $duration</p>";
      for ( $j = 0, $base = intval($start_tod / $period_minutes) * $period_minutes; $j < $duration; $j += $period_minutes ) {
        $tm[$our_dow][sprintf("m%d", $base + $j)] = "$ts->request_id/$ts->work_description" . ("$ts->entry_details" == "$ts->request_id" ? "" : "@|@$sow" );
      }
    }
  }

  $query = "SELECT *, date_part( 'dow', note_date ) AS dow FROM timesheet_note ";
  $query .= "WHERE note_by_id = $ts_user ";
  $query .= "AND note_date >= '" . date( 'Y-M-d', $sow ) . "' ";
  $query .= "AND note_date < '" . date( 'Y-M-d', $sow + (7 * 86400) ) . "' ";
  $query .= "ORDER BY note_date ASC; ";
  $result = awm_pgexec( $dbconn, $query, 'timesheet' );
  if ( $result && pg_NumRows($result) ) {
    for( $i = 0; $i < pg_NumRows($result); $i++ ) {
      $tn = pg_Fetch_Object( $result, $i );
      $tnote[$tm->dow] = $tn->note_detail ;
    }
  }


  // Now display the actual timesheet for entry
  echo '<form name=data method=post action="/timesheet.php" enctype="multipart/form-data"><table width="100%" border="0" cellpadding="1" cellspacing="2">' . "\n";
  echo "<tr class=row1><th class=cols>&nbsp;</th><th class=cols>Monday</th><th class=cols>Tuesday</th><th class=cols>Wednesday</th>";
  echo "<th class=cols>Thursday</th><th class=cols>Friday</th><th class=cols>Saturday</th><th class=cols>Sunday</th></tr>\n";
  for ( $tod = $sod, $r=0; $tod < $eod; $tod += $period_minutes, $r++ ) {
    printf( "<tr class=row%d>\n<th>%02d:%02d</th>\n", $r % 2, $tod / 60, $tod % 60 );
    for ( $dow=0; $dow < 7; $dow++ ) {
      echo "<td><input tabindex=$dow$tod type=text size=14 name=\"tm[$dow][m$tod]\" value=\"";
      echo $tm[$dow]["m$tod"];
      echo "\">&nbsp;</td>\n";
    }
    echo "</tr>\n";
  }
  echo "<tr><td>&nbsp;</td>";
  for ( $dow=0; $dow < 7; $dow++ ) {
    echo "<td><textarea rows=4 cols=10 name=\"tnote[$dow]\">";
    echo $tnote[$dow];
    echo "</textarea></td>\n";
  }
  echo "</tr>\n";
  echo "<tr><td><input type=hidden name=sow value=$sow><input type=hidden name=eod value=$eod></td><td align=center><input type=submit name=submit value=submit class=submit></td>";
  echo "<td colspan=6>Enter times as [WR#]/[Description], e.g. \"1537/Made the tea\".  Where you work on
  the same thing for several periods, you need only enter the description against the first entry for each day.</td></tr>\n";
  echo "</table>\n</form>\n";

  // Display a list of W/R's this person has worked on recently
  $subselect = "SELECT request_id FROM request_timesheet WHERE work_by_id = $ts_user AND request.request_id = request_timesheet.request_id ";
  $subselect .= "AND work_on >= '" . date( 'Y-M-d', $sow - (28 * 86400) ) . "' ";
  $subselect .= "AND work_on < '" . date( 'Y-M-d', $sow + (14 * 86400) ) . "' ";
  $query = "SELECT * ";
  $query .= "FROM request, usr, organisation, work_system ";
  $query .= "WHERE EXISTS( $subselect ) ";
  $query .= "AND request.requester_id = usr.user_no ";
  $query .= "AND usr.org_code = organisation.org_code ";
  $query .= "AND request.system_code = work_system.system_code ";
  $query .= "ORDER BY request_id ASC; ";
  $result = awm_pgexec( $dbconn, $query, 'timesheet' );
  if ( $result && pg_NumRows($result) ) {
    echo "<h3>Recent Requests You Have Worked On</h3>\n";
    echo '<table width="100%" border="0" cellpadding="1" cellspacing="2">';
    echo "<tr class=row1><th class=cols>WR #</th><th class=cols align=left>For</th><th class=cols align=left>System</th><th class=cols align=left>Request</th></tr>\n";
    for( $i=0; $i < pg_NumRows($result); $i++ ) {
      $wr = pg_Fetch_Object( $result, $i );
      echo "<tr class=row" . $i%2 . "><th><a href=request.php?request_id=$wr->request_id>$wr->request_id</a></th><td>$wr->abbreviation</td><td>$wr->system_desc</td><td>$wr->brief</td></tr>\n";
    }
    echo "</table>\n";
  }

  // Close off page and write the $settings out
  include("inc/footers.php");

?>