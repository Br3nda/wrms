<?php
  include("inc/always.php");
  include("inc/options.php");
  include("inc/code-list.php");

  if ( isset($system_code) && $system_code == "." ) unset( $system_code );

  $title = "$system_name Request List";
  include("inc/headers.php");

  if ( ! $roles['wrms']['Request'] || "$error_msg$error_qry" != "" ) {
    include( "inc/error.php" );
  }
  else {
    if ( !isset( $style ) || "$style" != "plain"  ) {
      echo "<form Action=\"$base_url/requestlist.php";
      if ( "$org_code" != "" ) echo "?org_code=$org_code";
      echo "\" Method=\"POST\">";
      echo "</h3>\n";

      include("inc/system-list.php");
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
        $system_list = get_system_list( "", "$system_code", 35);
      else
        $system_list = get_system_list( "CES", "$system_code", 35);
?>
<table border=0 cellspacing=0 cellpadding=2 align=center bgcolor="<?php echo $colors[6]; ?>">
<tr>
<td><table border=0 cellspacing=0 cellpadding=0><tr valign=middle><td class=smb>Find:</td><td class=sml><font size=1><input class=sml TYPE="Text" Size="8" Name="search_for" Value="<?php echo "$search_for"; ?>"></font></td>
<td class=smb>&nbsp;System:</td><td class=sml><font size=1><select class=sml name=system_code><option value=".">--- All Systems ---</option><?php echo "$system_list"; ?></select></font></td>
<?php
    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
      include( "inc/organisation-list.php" );
      $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . get_organisation_list( "$org_code", 30 );
      echo "<td class=smb>&nbsp;Organisation:</td><td class=sml><select class=sml name=\"org_code\">\n$orglist</select></td>\n";
    }
?>
</tr></table></td></tr>
<tr><td>
<?php
  $query = "SELECT * FROM lookup_code WHERE source_table='request' ";
  $query .= " AND source_field='status_code' ";
  $query .= " ORDER BY source_table, source_field, lookup_seq, lookup_code ";
  $rid = pg_Exec( $wrms_db, $query);
  if ( $rid && pg_NumRows($rid) > 1 ) {
    echo "\n<table border=0 cellspacing=0 cellpadding=0><tr valign=middle><td class=smb align=right valign=top>When:</td><td class=sml valign=top>\n";
    for ( $i=0; $i<pg_NumRows($rid); $i++ ) {
      $status = pg_Fetch_Object( $rid, $i );
      echo "<input type=checkbox name=incstat[$status->lookup_code]";
      if ( !isset( $incstat) || $incstat[$status->lookup_code] <> "" ) echo " checked";
      echo " value=1>" . str_replace( " ", "&nbsp;", $status->lookup_desc) . " ";
    }
    echo "<input type=checkbox name=inactive";
    if ( isset($inactive) ) echo " checked";
    echo " value=1>Inactive";
//    echo "</td>\n</tr></table></td></tr>";
    echo "</td></tr></table>";
  }
  $request_types = get_code_list( "request", "request_type", "$type_code" );
?>
</td></tr>
<tr><td><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>
<td class=smb align=right>Last&nbsp;Action&nbsp;From:</td>
<td nowrap class=smb><input type=text size=10 name=from_date class=sml value="<?php echo "$from_date"; ?>">
<a href="javascript:show_calendar('forms[0].from_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/images/date-picker.gif" border=0></a>
</td>

<td class=smb align=right>&nbsp;To:</td>
<td nowrap class=smb><input type=text size=10 name=to_date class=sml value="<?php echo "$to_date"; ?>">
<a href="javascript:show_calendar('forms[0].to_date');" onmouseover="window.status='Date Picker';return true;" onmouseout="window.status='';return true;"><img valign="middle" src="/images/date-picker.gif" border=0></a>
</td>
<td class=smb align=right>&nbsp;Type:</td>
<td nowrap class=smb><select name="type_code"><option value="">-- All Types --</option><?php echo "$request_types"; ?></select></td>
<td valign=middle class=smb align=center><input type=submit value="RUN QUERY" alt=go name=submit class="submit"></td>
</tr></table>
</td>
</tr></table>
</form>

<?php
  } // if  not plain style

  if ( "$search_for$org_code$system_code " != "" ) {
    // Recommended way of limiting queries to not include sub-tables for 7.1
    $result = awm_pgexec( $wrms_db, "SET SQL_Inheritance TO OFF;" );

    $query = "SELECT request_id, brief, fullname, email, lookup_desc AS status_desc, last_activity, detailed ";
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
    if ( "$type_code" != "" )     $query .= " AND request_type=" . intval($type_code);
    error_log( "type_code = >>$type_code<<", 0);

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
    $result = awm_pgexec( $wrms_db, $query, "requestlist", false, 7 );
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
//        if ( "$thisrequest->brief" == "" ) echo "-- no description --";
        if ( "$thisrequest->brief" == "" ) echo substr( $thisrequest->detailed, 0, 50) . "...";
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

