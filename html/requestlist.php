<?php
  include("inc/always.php");
  include("inc/options.php");

  $title = "$system_name Request List";
  include("inc/starthead.php");
  include("inc/styledef.php");
  include("inc/bodydef.php");
  include("inc/menuhead.php");

  if ( ! $roles[wrms][Request] || "$error_msg$error_qry" != "" ) {
    include( "inc/error.php" );
  }
  else {
    echo "<h3>Request List</h3>\n";
    echo "<form Action=\"$base_url/requestlist.php";
    if ( "$org_code" != "" ) echo "?org_code=$org_code";
    echo "\" Method=\"POST\">\n";

    include("inc/system-list.php");
    $system_list = get_system_list( "CES", "$system_code");
?>
<table><tr valign=middle>
<td><b>Name</b><input TYPE="Text" Size="10" Name="search_for" Value="<?php echo "$search_for"; ?>"></td>
<td> <b>System</b><select NAME=system_code><?php echo "$system_list"; ?></select></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="44" BORDER="0" HEIGHT="26" name="submit"></td>
</tr></table>
</form>  

<?php
  if ( "$search_for$org_code" != "" ) {
    $query = "SELECT request_id, brief, fullname, email, lookup_desc AS status_desc FROM request, usr, lookup_code AS status ";
    $query .= " WHERE request.request_by=usr.username ";
    if ( "$inactive" == "" )        $query .= " AND active ";
    if ( "$org_code" != "" )        $query .= " AND org_code='$org_code' ";
    if ( "$search_for" != "" ) {
      $query .= " AND (brief ~* '$search_for' ";
      $query .= " OR detailed ~* '$search_for' ) ";
    }
    if ( "$system_code" != "" )     $query .= " AND system_code='$system_code' ";
    $query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";
    $query .= " ORDER BY request_id DESC ";
    $query .= " LIMIT 100 ";
    $result = pg_Exec( $wrms_db, $query );
    if ( ! $result ) {
      $error_loc = "requestlist.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    else {
      echo "<p>" . pg_NumRows($result) . " requests found</p>"; // <p>$query</p>";
      echo "<table border=\"0\" align=center><tr>\n";
      echo "<th>WR &nbsp;#</th><th>Requested By</th>";
      echo "<th>Description</th><th>Status</th><th>Actions</th></tr>";

      // Build table of requests found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisrequest = pg_Fetch_Object( $result, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td class=sml align=center><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->request_id</a></td>\n";
        echo "<td class=sml><a href=\"mailto:$thisrequest->pemail\">$thisrequest->fullname</a></td>\n";
        echo "<td class=sml><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->brief";
        if ( "$thisrequest->brief" == "" ) echo "-- no description --";
        echo "</a></td>\n";
        echo "<td class=sml>$thisrequest->status_desc</td>\n";
        echo "<td class=sml><a href=\"form.php?request_id=$thisrequest->request_id&form=note\">Note</a>&nbsp;\n";
        echo "<a href=\"form.php?request_id=$thisrequest->request_id&form=time\">Time</a></td>\n";

        echo "</tr>\n";
      }
      echo "</table><p>$query</p>\n";
    }
  }

} /* The end of the else ... clause waaay up there! */ ?>
</body> 
</html>


