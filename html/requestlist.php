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
    echo "<form Action=\"$base_url/requestlist.php\" Method=\"POST\">\n";
?>
<table><tr valign=middle>
<td><b>Name&nbsp;</b><input TYPE="Text" Size="20" Name="search_for" Value="<?php echo "$search_for"; ?>"></td>
<td><b>System&nbsp;</b><select NAME=system_code>
<?php
  $query = "SELECT system_code, lookup_desc FROM system_usr, lookup_code ";
  $query .= " WHERE source_table='user' AND source_field='system_code' ";
  $query .= " AND lookup_code=system_code ";
  $query .= " AND user_no=$session->user_no ";
  $query .= " ORDER BY source_table, source_field, lookup_seq ";
  $result = pg_Exec( $wrms_db, $query );
  if ( ! $result ) {
    $error_loc = "requestlist.php";
    $error_qry = "$query";
    include("inc/error.php");
  }
  else {
    // Build table of systems found
    for ( $i=0; $i < pg_NumRows($result); $i++ ) {
      $rsys = pg_Fetch_Object( $result, $i );
      echo "<option value=\"$system_code->system_code\"";
      if ( "$system_code" == "$rsys->system_code" ) echo " SELECTED";
      echo ">$rsys->lookup_desc</option>\n";
    }
  }
?>
</select></td>
<td><input TYPE="Image" src="images/in-go.gif" alt="go" WIDTH="44" BORDER="0" HEIGHT="26" name="submit"></td>
</tr></table>
</form>  

<?php
  if ( "$search_for" != "" ) {
    $query = "SELECT request_id, brief, fullname, email, lookup_desc AS status_desc FROM request, usr, lookup_code AS status ";
    $query .= " WHERE system_code='$system_code' ";
    $query .= " AND active ";
    $query .= " AND (brief ~* '$search_for' ";
    $query .= " OR detailed ~* '$search_for' ) ";
    $query .= " AND request.request_by=usr.username ";
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
      echo "<p>" . pg_NumRows($result) . " requests found</p>";
      echo "<table border=\"0\" align=system_code><tr>\n";
      echo "<th>Request&nbsp;#</th><th>Requested By</th>";
      echo "<th>Description</th><th>EMail</th><th>Actions</th></tr>";

      // Build table of requests found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisrequest = pg_Fetch_Object( $result, $i );

        if(floor($i/2)-($i/2)==0) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td class=sml align=center><a href=\"request.php?requestno=$thisrequest->request_id\">$thisrequest->request_id</a></td>\n";
        echo "<td class=sml><a href=\"mailto:$thisrequest->pemail\">$thisrequest->fullname</a></td>\n";
        echo "<td class=sml><a href=\"request.php?requestno=$thisrequest->request_id\">$thisrequest->brief";
        if ( "$thisrequest->brief" == "" ) echo "-- no description --";
        echo "</a></td>\n";
        echo "<td class=sml>$thisrequest->status_desc</td>\n";
        echo "<td class=sml><a href=\"form.php?requestno=$thisrequest->requestno&form=training\">Training</a>&nbsp;\n";
        echo "<a href=\"form.php?requestno=$thisrequest->requestno&form=transfer\">Transfer</a></td>\n";

        echo "</tr>\n";
      }
      echo "</table>\n";
    }
  }

} /* The end of the else ... clause waaay up there! */ ?>
</body> 
</html>


