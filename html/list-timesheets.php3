<?php
  include( "awm-auth.php3" );
  $title = "List Work Done";
  include("$homedir/apms-header.php3"); 
  require("$funcdir/nice_date-func.php3");
  require("$funcdir/html_format-func.php3");
?>
<TABLE BORDER=1 WIDTH=100%>
<TR><TH ALIGN=LEFT>Done On</TH><TH ALIGN=LEFT>Duration</TH>
<TH ALIGN=LEFT>Done By</TH><TH ALIGN=LEFT>Work for</TH>
<TH ALIGN=LEFT>Charged on</TH><TH ALIGN=LEFT>Description</TH></TR>

<?php /**** data rows in the usr table... */

function nice_time( $in_time ) {
  /* does nothing yet... */
  return "$in_time";
}
  $query = "SELECT * FROM request_timesheet, request, awm_perorg_rel, awm_perorg ";
  $query .= " WHERE request_timesheet.request_id = request.request_id ";
  $query .= "AND awm_perorg_rel.perorg_rel_id = requester_id ";
  $query .= "AND awm_perorg_rel.perorg_rel_type = 'Employer' ";
  $query .= "AND awm_perorg.perorg_id = awm_perorg_rel.perorg_id";
  $query .= " ORDER BY work_on DESC";
  $rid = pg_Exec( $dbid,$query );
  $rows = pg_NumRows( $rid );
  for ( $i=0; $i < $rows; $i++ ) {
    $timesheet = pg_Fetch_Object( $rid, $i );
    echo "<TR>";
    echo "<TD>" . substr( nice_date($timesheet->work_on), 7) . "</TD>";
    echo "<TD>" . nice_time($timesheet->work_duration) . "</TD>";
    echo "<TD>$timesheet->work_by</TD>";
    echo "<TD>$timesheet->perorg_name</TD>";
    if ( "$timesheet->charged_on" == "" )
      echo "<TD>uncharged</TD>";
    else
      echo "<TD>" . substr( nice_date($timesheet->charged_on), 7) . "</TD>";
    echo "<TD>" . html_format( $timesheet->work_description) . " <I> <A HREF=modify-request.php3?request_id=$timesheet->request_id>(WR #$timesheet->request_id)</A></I></TD>";
    echo "</TR>\n";
  }
?>

</TABLE>


<?php
  include("apms-footer.php3");
?>

