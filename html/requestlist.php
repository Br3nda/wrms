<?php
  include("inc/always.php");
  include("inc/options.php");

  if ( isset($system_code) && $system_code == "." ) unset( $system_code );

  $title = "$system_name Request List";
  include("inc/headers.php");

  if ( ! $roles['wrms']['Request'] || "$error_msg$error_qry" != "" ) {
    include( "inc/error.php" );
  }
  else {
    echo "<h3>Request List";
    if ( isset( $style ) && "$style" == "plain"  ) {
      echo "</h3>\n";
    }
    else {
      echo "<form Action=\"$base_url/requestlist.php";
      if ( "$org_code" != "" ) echo "?org_code=$org_code";
      echo "\" Method=\"POST\">";
      echo "</h3>\n";

      include("inc/system-list.php");
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
        $system_list = get_system_list( "", "$system_code", 50);
      else
        $system_list = get_system_list( "CES", "$system_code", 50);
?>
<table border=0 cellspacing=0 cellpadding=2 align=center bgcolor=<?php echo $colors[6]; ?>>
<tr valign=middle>
<td class=smb align=right>Search:</td><td class=sml><font size=1><input class=sml TYPE="Text" Size="10" Name="search_for" Value="<?php echo "$search_for"; ?>"></font></td>
<td class=smb align=right>&nbsp;System:</td><td class=sml><font size=1><select class=sml name=system_code><option value=".">All Systems</option><?php echo "$system_list"; ?></select></font></td>

<td class=smb align=right>Last&nbsp;Activity&nbsp;From:</td>
<td nowrap class=smb><input type=text size=10 name=from_date class=sml value="<?php echo "$from_date"; ?>">
<a href="javascript:show_calendar('forms[0].from_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/images/date-picker.gif" border=0></a>
</td>


<td class=smb align=right>&nbsp;To:</td>
<td nowrap class=smb><input type=text size=10 name=to_date class=sml value="<?php echo "$to_date"; ?>">
<a href="javascript:show_calendar('forms[0].to_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/images/date-picker.gif" border=0></a>
</td>

<td rowspan=2 valign=middle class=sml>&nbsp;<BR><input TYPE=Image src=images/in-go.gif alt=go WIDTH=44 BORDER=0 HEIGHT=26 name=submit></td></tr>
<?php
  $query = "SELECT * FROM lookup_code WHERE source_table='request' ";
  $query .= " AND source_field='status_code' ";
  $query .= " ORDER BY source_table, source_field, lookup_seq, lookup_code ";
  $rid = pg_Exec( $wrms_db, $query);
  if ( ! $rid ) {
    echo "<p>$query";
  }
  else if ( pg_NumRows($rid) > 1 ) {
    echo "<tr valign=middle><td class=smb align=right valign=top>Statuses:</td><td colspan=7 class=sml valign=top>\n";
    for ( $i=0; $i<pg_NumRows($rid); $i++ ) {
      $status = pg_Fetch_Object( $rid, $i );
      echo "<input type=checkbox name=incstat[$status->lookup_code]";
      if ( !isset( $incstat) || $incstat[$status->lookup_code] <> "" ) echo " checked";
      echo " value=1>&nbsp;" . str_replace( " ", "&nbsp;", $status->lookup_desc) . " &nbsp; ";
    }
    echo "<input type=checkbox name=inactive";
    if ( isset($inactive) ) echo " checked";
    echo " value=1>&nbsp;Inactive";
    echo "</td>\n</tr>";
  }
?>
</table>
</form>

<?php
  } // if  not plain style

  if ( "$search_for$org_code$system_code " != "" ) {
    $query = "SELECT request_id, brief, fullname, email, lookup_desc AS status_desc, last_activity ";
    $query .= "FROM request, usr, lookup_code AS status ";

    $query .= " WHERE request.request_by=usr.username ";
    if ( "$inactive" == "" )        $query .= " AND active ";
    if (! ($roles['wrms']['Admin'] || $roles['wrms']['Admin']) )
      $query .= " AND org_code = '$session->org_code' ";
    else if ( "$org_code" != "" )
      $query .= " AND org_code='$org_code' ";

    if ( "$user_no" <> "" )
      $query .= " AND requester_id = $user_no ";
    else if ( "$interested" <> "" )
      $query .= " AND request_interested.request_id=request.request_id AND request_interested.user_no = $interested ";
    else if ( "$allocated_to" <> "" )
      $query .= " AND request_allocated.request_id=request.request_id AND request_allocated.allocated_to_id = $allocated_to ";

    if ( "$search_for" != "" ) {
      $query .= " AND (brief ~* '$search_for' ";
      $query .= " OR detailed ~* '$search_for' ) ";
    }
    if ( "$system_code" != "" )     $query .= " AND system_code='$system_code' ";

    if ( "$from_date" != "" )     $query .= " AND request.last_activity>='$from_date' ";

    if ( "$to_date" != "" )     $query .= " AND request.last_activity<='$to_date' ";

    if ( isset( $incstat) ) {
      $query .= " AND (request.last_status ~* '[";
      while( list( $k, $v) = each( $incstat ) ) {
        $query .= $k ;
      }
      $query .= "]') ";
    }


    $query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";
    $query .= " ORDER BY request_id DESC ";
    $query .= " LIMIT 100 ";
    $result = awm_pgexec( $wrms_db, $query );
    if ( $result ) {
      echo "<p>" . pg_NumRows($result) . " requests found</p>"; // <p>$query</p>";
      echo "<table border=\"0\" align=left><tr>\n";
      echo "<th class=cols>WR&nbsp;#</th><th class=cols>Requested By</th>";
      echo "<th class=cols>Description</th><th class=cols>Status</th><th class=cols>Actions</th>";
      echo "<th class=cols>Last Activity</th>";
      echo "</tr>";

      // Build table of requests found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisrequest = pg_Fetch_Object( $result, $i );

        if ( ($i % 2) == 0 ) echo "<tr bgcolor=$colors[6]>";
        else echo "<tr bgcolor=$colors[7]>";

        echo "<td class=sml align=center><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->request_id</a></td>\n";
        echo "<td class=sml nowrap><a href=\"mailto:$thisrequest->email\">$thisrequest->fullname</a></td>\n";
        echo "<td class=sml><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->brief";
        if ( "$thisrequest->brief" == "" ) echo "-- no description --";
        echo "</a></td>\n";
        echo "<td class=sml>$thisrequest->status_desc</td>\n";
        echo "<td class=sml nowrap>&nbsp;</td>\n";
        echo "<td class=sml>" . substr(nice_date($thisrequest->last_activity),7) . "</td>\n";

        echo "</tr>\n";
      }
      echo "</table>\n";
    }
  }

} /* The end of the else ... clause waaay up there! */

include("inc/footers.php");

?>

