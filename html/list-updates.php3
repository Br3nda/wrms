<?php
  include( "awm-auth.php3" );
  $title = "List Updates";
  include("apms-header.php3"); 
  require("$funcdir/nice_date-func.php3");
  require("$funcdir/html_format-func.php3");
?>


<TABLE BORDER=2 WIDTH=100%>
<TR><TH>ID</TH><TH>Done By</TH><TH>Done On</TH><TH>Description</TH><TH></TH></TR>

<?php /**** data rows in the usr table... */
  $query = "SELECT * FROM system_update ORDER BY system_update.update_id DESC";
  $rid = pg_Exec( $dbid,$query );
  $rows = pg_NumRows( $rid );
  for ( $i=0; $i < $rows; $i++ ) {
    $update = pg_Fetch_Object( $rid, $i );
    echo "<TR>";
    echo "<TH VALIGN=TOP ALIGN=CENTER ROWSPAN=2><FONT SIZE=+2>$update->update_id</FONT></TH>\n";
    echo "<TD>$update->update_by</TD>\n";
    echo "<TD>" . nice_date($update->update_on) . "</TD>\n";
    echo "<TD><A HREF=\"view-update.php3?update_id=$update->update_id&username=$usr->username\">";
    echo "$update->update_brief</A></TD>";
    echo "<TD><A HREF=\"$update->file_url\">Download</A></TD>\n";
    echo "</TR>\n";
    echo "<TR><TD COLSPAN=4>";
    echo html_format( $update->update_description) . "</TD></TR><TR><TD></TD></TR>";
  }
?>

</TABLE>


<?php
  include("apms-footer.php3");
?>

