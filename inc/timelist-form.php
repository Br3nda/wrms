<?php
  include("$base_dir/inc/nice-date.php");
  include("$base_dir/inc/html-format.php");
function nice_time( $in_time ) {
  /* does nothing yet... */
  return "$in_time";
}
?>
<P class=helptext>This page lists timesheets.</P>

<FORM METHOD=POST ACTION="<?php echo "$SCRIPT_NAME?form=$form"; ?>">
<table align=center><tr valign=middle>
<td><b>Name</b><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="44" BORDER="0" HEIGHT="26" name="submit"></td>
<td><label for=uncharged><input type=checkbox value=1 name=uncharged> Uncharged</label>
</tr></table>
</form>  

<?php
  if ( "$search_for$system_code " != "" ) {
    $query = "SELECT *, worker.fullname AS worker_name, requester.fullname AS requester_name";
    $query .= " FROM request_timesheet, request, usr AS worker, usr AS requester ";
    $query .= " WHERE request_timesheet.request_id = request.request_id";
    $query .= " AND worker.user_no = work_by_id ";
    $query .= " AND requester.user_no = requester_id ";

    if ( "$search_for" <> "" ) {
      $query .= " AND work_description ~* '$search_for' ";
    }
    if ( "$system_code" <> "" ) {
      $query .= " AND request.system_code='$system_code' ";
    }
    if ( "$org_code" <> "" ) {
      $query .= " AND requester.org_code='$org_code' ";
    }

    if ( "$after" != "" )
      $query .= " AND request_timesheet.work_on>'$after' ";
    if ( "$before" != "" )
      $query .= " AND request_timesheet.work_on<'$before' ";
    if ( "$uncharged" != "" )
      $query .= " AND request_timesheet.work_charged IS NULL ";
    $query .= " ORDER BY work_on DESC";
    $query .= " LIMIT 100 ";
    $result = pg_Exec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "timelist-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    else {
      echo "<p>" . pg_NumRows($result) . " timesheets found</p>"; // <p>$query</p>";
      echo "<table border=\"0\" align=center><tr>\n";
      echo "<th class=cols>Done On</th><th class=cols>Duration</th>";
      echo "<th class=cols>Done By</th><th class=cols>Work for</th>";
      echo "<th class=cols>Charged on</th><th class=cols>Description</th></tr>";

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $timesheet = pg_Fetch_Object( $result, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td class=sml>" . substr( nice_date($timesheet->work_on), 7) . "</td>";
        echo "<td class=sml>" . nice_time($timesheet->work_duration) . "</td>";
        echo "<td class=sml>$timesheet->worker_name</td>";
        echo "<td class=sml>$timesheet->requester_name</td>";
        if ( "$timesheet->work_charged" == "" )
          echo "<td class=sml>uncharged</td>";
        else
          echo "<td class=sml>" . substr( nice_date($timesheet->work_charged), 7) . "</td>";
        echo "<td class=sml>" . html_format( $timesheet->work_description) . " <I> <A HREF=$base_url/request.php?request_id=$timesheet->request_id>(WR #$timesheet->request_id)</A></I></td>";

        echo "</tr>\n";
      }
      echo "</table>\n";
    }
  }
?>
</TABLE>
</FORM>

