<?php
  require_once("always.php");
  require_once("authorisation-page.php");
  include("options.php");
  include("tidy.php");

  $debuglevel = 5;
  if ( "$submit" <> "") {
    include("timesheet-valid.php");
    if ( "$because" == "" ) include("timesheet-action.php");
  }

  $title = "$system_name - Weekly Timesheet: $session->fullname";
  $right_panel = false;
  require_once("top-menu-bar.php");
  include("headers.php");

// Helper function to tidy the automatic remembrance of our numeric settings
// $settings are saved to the user record within "footers.php"
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
  $latest_week = 86400 + mktime( 0, 0, 0, $latest_date['tm_mon'] + 1, $latest_date['tm_mday'], $latest_date['tm_year'], 0) - (86400 * $latest_date['tm_wday']);

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
  $to = 1425;
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
  echo "<th>From:</th><td>" . build_time_list('sod', 0, $sod, 30) . "</td>\n";
  echo "<th>To:</th><td>" . build_time_list('eod', $sod + 240, $eod, max($period_minutes / 4, 30)) . "</td>\n";
  echo "</tr>\n";
  echo "</table>\n</form>\n";


  // Read the timesheets from the file and build reasonable bits from them
  // The funky "AT TIME ZONE 'GMT'" weirdness is required from the switch to 7.4.1
  // although now we're doing this, the CAST ... could be removed.
  //    $pg_version should be in the config.php
  $at_time_zone = ( isset($pg_version) && $pg_version >= 7.4 ? "AT TIME ZONE 'GMT'" : "" );
  $ts_user = intval($session->user_no);
  $query = "SELECT *, EXTRACT( EPOCH FROM CAST ( work_on $at_time_zone AS TIMESTAMP WITHOUT TIME ZONE ) ) AS started, ";
  $query .= "EXTRACT( EPOCH FROM CAST ( (work_on $at_time_zone + work_duration) AS TIMESTAMP WITHOUT TIME ZONE ) ) AS finished, ";
  $query .= "EXTRACT( DOW FROM work_on ) AS dow, 0 AS offset ";
  $query .= "FROM request_timesheet WHERE work_by_id = $ts_user ";
  $query .= "AND work_on >= '" . date( 'Y-M-d', $sow ) . "' ";
  $query .= "AND work_on < '" . date( 'Y-M-d', $sow + (7 * 86400) ) . "' ";
  $query .= "ORDER BY work_on ASC; ";

  // The query above requires 7.2, so the below (including hacks with $ts->offset) works with 7.0
  // currently in production...
  // $query = "SELECT *, date_part( 'epoch', work_on ) AS started, ";
  // $query .= "date_part( 'epoch', (work_on + work_duration)) AS finished, ";
  // $query .= "date_part( 'dow', work_on ) AS dow, ";
  // $query .= "date_part( 'epoch', '1970-1-1'::timestamp) AS offset ";
  // $query .= "FROM request_timesheet WHERE work_by_id = $ts_user ";
  // $query .= "AND work_on >= '" . date( 'Y-M-d', $sow ) . "' ";
  // $query .= "AND work_on < '" . date( 'Y-M-d', $sow + (7 * 86400) ) . "' ";
  // $query .= "ORDER BY work_on ASC; ";
  $result = awm_pgexec( $dbconn, $query, 'timesheet' );
  if ( $result && pg_NumRows($result) ) {

    // Construct timesheet entries from all of our existing work data for the week
    // Entries before the start of the day, or after the end of the day are forced within
    // the work period.
    for( $i = 0; $i < pg_NumRows($result); $i++ ) {
      $ts = pg_Fetch_Object( $result, $i );
      $our_dow = ($ts->dow + 6) % 7;
      $start_tod = intval( (($ts->started + $ts->offset) % 86400) / 60 );
      $finish_tod = intval( (($ts->finished + $ts->offset) % 86400) / 60 );
      $duration = $finish_tod - $start_tod;

      if ( $duration == 0 || "$ts->finished" == "" || ($start_tod + $duration) == 0 ) {
        if ( "$ts->work_units" == "hours" )  $duration = $ts->work_quantity * 60;
      }
      if ( $duration == 0 ) continue;

      // Force time before end of this person's day...
      if ( $finish_tod > $eod ) {
        $finish_tod = $eod;
        $start_tod = $eod - $duration;
      }

      // Normalise that to always start on a period boundary...
      $start_tod = $sod + $period_minutes * intval(( $start_tod - $sod ) / $period_minutes ) ;
      // error_log( "timesheet: DBG: ts_start=$ts->started, start_tod=$start_tod, sod=$sod, period=$period_minutes" );

      // Force time later than start of person's day
      if ( $start_tod < $sod ) {
        $start_tod = $sod;
        $finish_tod = $sod + $duration;
      }

      //
      for ( $j = 0; $j < $duration; $j += $period_minutes ) {
        $tm[$our_dow][sprintf("m%d", $start_tod + $j)] = "$ts->request_id/$ts->work_description" . ("$ts->entry_details" == "$ts->request_id" ? "" : "@|@$sow" );
        // error_log( "timesheet: DBG: \$" . "tm[$our_dow][" . sprintf("m%d", $start_tod + $j) . "] = $ts->request_id/$ts->work_description" . ("$ts->entry_details" == "$ts->request_id" ? "" : "@|@$sow" ) );
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
  echo <<<EOHEADERS
<form name=data method="post" action="/timesheet.php" enctype="multipart/form-data" style="display:inline;">
<table width="100%" border="0" cellpadding="1" cellspacing="0">
<tr>
 <th class="cols" width="9%">&nbsp;</th>
 <th class="cols" width="13%">Monday</th>
 <th class="cols" width="13%">Tuesday</th>
 <th class="cols" width="13%">Wednesday</th>
 <th class="cols" width="13%">Thursday</th>
 <th class="cols" width="13%">Friday</th>
 <th class="cols" width="13%">Saturday</th>
 <th class="cols" width="13%">Sunday</th>
</tr>
EOHEADERS;

  for ( $tod = $sod, $r=0; $tod < $eod; $tod += $period_minutes, $r++ ) {
    printf( '<tr class="row%d"><th>%02d:%02d</th>', $r % 2, $tod / 60, $tod % 60 );
    for ( $dow=0; $dow < 7; $dow++ ) {
      $tabindex = ($dow * 200) + ($tod / 10);
      echo "<td><input tabindex=\"$tabindex\" type=\"text\" size=\"14\" name=\"tm[$dow][m$tod]\" value=\"";
      echo $tm[$dow]["m$tod"];
      echo "\"></td>\n";
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
  include("footers.php");

?>