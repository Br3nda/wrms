<?php
  include("inc/always.php");
  include("inc/options.php");
  include("inc/code-list.php");
  include( "$base_dir/inc/user-list.php" );

  if ( isset($system_code) && $system_code == "." ) unset( $system_code );

  $title = "$system_name Request List";
  include("inc/headers.php");

  if ( !isset($rlsort) ) $rlsort = $settings->get('rlsort');
  if ( !isset($rlseq) ) $rlseq = $settings->get('rlseq');
  if ( "$rlsort" == "" ) $rlsort = "request_id";
  $rlseq = strtoupper($rlseq);
  if ( "$rlseq" == "" ) {
    $rlseq = ( "$rlsort" == "request_id" || "$rlsort" == "request_on" || "$rlsort" == "last_change" ? "DESC" : "ASC");
  }
  if ( "$rlseq" != "ASC" ) $rlseq = "DESC";
  $settings->set('rlsort', $rlsort);
  $settings->set('rlseq', $rlseq);

  if ( isset($org_code) ) $org_code = intval($org_code);
  if ( isset($org_code) ) $org_code = intval($org_code);

function column_header( $ftext, $fname ) {
  global $rlsort, $rlseq, $org_code, $system_code, $search_for;
  echo "<th class=cols><a class=cols href=\"$PHP_SELF?rlsort=$fname&rlseq=";
  if ( "$rlsort" == "$fname" ) echo ( "$rlseq" == "DESC" ? "ASC" : "DESC");
  if ( isset($org_code) ) echo "&org_code=$org_code";
  if ( isset($system_code) ) echo "&system_code=$system_code";
  if ( isset($search_for) ) echo "&search_for=$search_for";
  echo "\">$ftext";
  if ( "$rlsort" =="$fname" ) {
    echo "&nbsp;<img border=0 src=\"images/sort-$rlseq.png\">";
  }
  echo "</th>";
}

if ( ! $roles['wrms']['Request'] || "$error_msg$error_qry" != "" ) {
  include( "inc/error.php" );
}
else {
  if ( !isset( $style ) || "$style" != "plain"  ) {
    echo "<form Action=\"$base_url/requestlist.php";
    if ( "$org_code$qs" != "" ) {
      echo "?";
      if ( "$org_code" != "" ) echo "org_code=$org_code" . ( "$qs" == "" ? "" : "&");
      if ( "$qs" != "" ) echo "qs=$qs";
    }
    echo "\" Method=\"POST\">";
    echo "</h3>\n";

    include("inc/system-list.php");
    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
      $system_list = get_system_list( "", "$system_code", 35);
    else
      $system_list = get_system_list( "CES", "$system_code", 35);

    echo "<table border=0 cellspacing=2 cellpadding=0 align=center class=row0 width=100% style=\"border: 1px dashed #aaaaaa;\">\n<tr>\n";
    echo "<td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";
    if ( "$qs" == "complex" )
      echo "<td class=smb>Find:</td><td class=sml><input class=sml type=text size=10 name=search_for value=\"$search_for\"></td>\n";

    echo "<td class=smb>&nbsp;System:</td><td class=sml><select class=sml name=system_code><option value=\".\">--- All Systems ---</option>$system_list</select></td>\n";

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    include( "inc/organisation-list.php" );
    $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . get_organisation_list( "$org_code", 30 );
    echo "<td class=smb>&nbsp;Organisation:</td><td class=sml><select class=sml name=\"org_code\">\n$orglist</select></td>\n";
  }
  if ( "$qs" != "complex" )
   echo "<td valign=middle class=smb align=center><input type=submit value=\"RUN QUERY\" alt=go name=submit class=\"submit\"></td><td class=smb width=100px> &nbsp; &nbsp; &nbsp; </td>\n";
  echo "</tr></table></td></tr>\n";


  if ( "$qs" == "complex" ) {
    if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] || $roles['wrms']['Manage'] ) {
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
        $user_org_code = "";
      }
      else {
        $user_org_code = "$session->org_code";
      }
      echo "<tr><td width=100%><table border=0 cellspacing=0 cellpadding=0 width=100%><tr valign=middle>\n";
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support']  || $roles['wrms']['Manage']) {
        $user_list = "<option value=\"\">--- Any Requester ---</option>" . get_user_list( "", $user_org_code, "" );
        echo "<td class=smb>By:</td><td class=sml><select class=sml name=requested_by>$user_list</select></td>\n";
        if ( !($roles['wrms']['Admin'] || $roles['wrms']['Support']) && !isset($interested_in) ) $interested_in = $session->user_no;
        $user_list = "<option value=\"\">--- Any Interested User ---</option>" . get_user_list( "", $user_org_code, $interested_in );
        echo "<td class=smb>Watching:</td><td class=sml><select class=sml name=interested_in>$user_list</select></td>\n";
      }
      if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
        if ( !isset($allocated_to) ) $allocated_to = $session->user_no;
        $user_list = "<option value=\"\">--- Any Assigned Staff ---</option>" . get_user_list( "Support", "", $allocated_to );
        echo "<td class=smb>ToDo:</td><td class=sml><select class=sml name=allocated_to>$user_list</select></td>\n";
      }
      echo "</tr></table></td></tr>\n";
    }
//  else if ( !isset($requested_by) )
//    $requested_by = $session->user_no;


    $request_types = get_code_list( "request", "request_type", "$type_code" );
?>
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
<td nowrap class=smb><select name="type_code" class=sml><option value="">-- All Types --</option><?php echo "$request_types"; ?></select></td>
</tr></table></td>
</tr>
<?php
    echo "<tr><td>\n";
    echo "<table border=0 cellspacing=0 cellpadding=0><tr valign=middle><td class=smb align=right valign=top>When:</td><td class=sml valign=top>\n";
    $query = "SELECT * FROM lookup_code WHERE source_table='request' ";
    $query .= " AND source_field='status_code' ";
    $query .= " ORDER BY source_table, source_field, lookup_seq, lookup_code ";
    $rid = pg_Exec( $wrms_db, $query);
    if ( $rid && pg_NumRows($rid) > 1 ) {
      $nrows = pg_NumRows($rid);
      for ( $i=0; $i<$nrows; $i++ ) {
        $status = pg_Fetch_Object( $rid, $i );
        echo "<input type=checkbox name=incstat[$status->lookup_code]";
        if ( !isset( $incstat) || $incstat[$status->lookup_code] <> "" ) echo " checked";
        echo " value=1>" . str_replace( " ", "&nbsp;", $status->lookup_desc) . " &nbsp; ";
        if ( $i == intval($nrows / 2) ) echo "&nbsp;<br>";
      }
      echo "<input type=checkbox name=inactive";
      if ( isset($inactive) ) echo " checked";
      echo " value=1>Inactive";
//      echo "</td>\n</tr></table></td></tr>";
      echo "c</td>\n";
    }
    echo "<td valign=middle class=smb align=center><input type=submit value=\"RUN QUERY\" alt=go name=submit class=\"submit\"></td>\n";
    echo "</tr></table>\n</td></tr>\n";

    echo "<tr><td>\n";
    echo "<table border=0 cellspacing=0 cellpadding=0 align=center>\n";
    echo "<tr valign=middle>\n";
    echo "<td valign=middle class=smb align=right>Save query as:</td><td class=sml valign=top>\n";
    echo "<td valign=middle class=smb align=center><input type=text size=20 value=\"$savelist\" name=savelist class=\"sml\"></td>\n";
    echo "<td valign=middle class=smb align=left><input type=submit value=\"SAVE QUERY\" alt=go name=submit class=\"submit\"></td>\n";
    echo "</tr></table>\n</td></tr>\n";
  }
?>
</table>
</form>

<?php
  } // if  not plain style


  /////////////////////////////////////////////////////////////////////////////////////////////////
  //
  // Now we build the statement that will find those requests...
  //
  /////////////////////////////////////////////////////////////////////////////////////////////////

  if ( "$search_for$org_code$system_code " != "" ) {
    // Recommended way of limiting queries to not include sub-tables for 7.1
    $result = awm_pgexec( $wrms_db, "SET SQL_Inheritance TO OFF;" );

    $query = "SELECT request.request_id, brief, fullname, email, request_on, status.lookup_desc AS status_desc, last_activity, detailed ";
    $query .= ", request_type.lookup_desc AS request_type_desc, lower(fullname) AS lfull, lower(brief) AS lbrief ";
    $query .= ", to_char( request.last_activity, 'FMdd Mon yyyy') AS last_change ";
    $query .= ", to_char( request.request_on, 'FMdd Mon yyyy') AS date_requested ";
    $query .= "FROM ";
    if ( intval("$interested_in") > 0 ) $query .= "request_interested, ";
    if ( intval("$allocated_to") > 0 ) $query .= "request_allocated, ";
    $query .= "request, usr, lookup_code AS status ";
    $query .= ", lookup_code AS request_type";

    $query .= " WHERE request.request_by=usr.username ";
    $query .= " AND request_type.source_table='request' AND request_type.source_field='request_type' AND request.request_type = request_type.lookup_code";
    if ( "$inactive" == "" )        $query .= " AND active ";
    if (! ($roles['wrms']['Admin'] || $roles['wrms']['Support']) )
      $query .= " AND org_code = '$session->org_code' ";
    else if ( isset($org_code) && intval($org_code) > 0 )
      $query .= " AND org_code='$org_code' ";

    if ( intval("$user_no") > 0 )
      $query .= " AND requester_id = " . intval($user_no);
    else if ( intval("$requested_by") > 0 )
      $query .= " AND requester_id = " . intval($requested_by);
    if ( intval("$interested_in") > 0 )
      $query .= " AND request_interested.request_id=request.request_id AND request_interested.user_no = " . intval($interested_in);
    if ( intval("$allocated_to") > 0 )
      $query .= " AND request_allocated.request_id=request.request_id AND request_allocated.allocated_to_id = " . intval($allocated_to);

    if ( "$search_for" != "" ) {
      $query .= " AND (brief ~* '$search_for' ";
      $query .= " OR detailed ~* '$search_for' ) ";
    }
    if ( "$system_code" != "" )     $query .= " AND system_code='$system_code' ";
    if ( "$type_code" != "" )     $query .= " AND request_type=" . intval($type_code);
    error_log( "type_code = >>$type_code<<", 0);

    if ( "$from_date" != "" )     $query .= " AND request.last_activity >= '$from_date' ";

    if ( "$to_date" != "" )     $query .= " AND request.last_activity<='$to_date' ";

    if ( isset( $incstat) ) {
      $query .= " AND (request.last_status ~* '[";
      while( list( $k, $v) = each( $incstat ) ) {
        $query .= $k ;
      }
      $query .= "]') ";
    }


    $query .= " AND status.source_table='request' AND status.source_field='status_code' AND status.lookup_code=request.last_status ";
    $query .= " ORDER BY $rlsort $rlseq ";
    $query .= " LIMIT 100 ";
    $result = awm_pgexec( $wrms_db, $query, "requestlist", false, 7 );
    if ( $result && pg_NumRows($result) > 0 )
      echo "<small>" . pg_NumRows($result) . " requests found</small>"; // <p>$query</p>";
    else
      echo "<small>No requests found</small><p>$query</p>";

    echo "<table border=\"0\" align=left>\n<tr>\n";
    column_header("WR&nbsp;#", "request_id");
    column_header("Request By", "lfull" );
    column_header("Request On", "request_on" );
    column_header("Description", "lbrief");
    column_header("Status", "status_desc" );
    column_header("Type", "request_type_desc" );
    column_header("Last Chng", "last_change");
    echo "</tr>";

    if ( $result ) {

      // Build table of requests found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $thisrequest = pg_Fetch_Object( $result, $i );

        printf( "<tr class=row%1d>\n", $i % 2);

        echo "<td class=sml align=center><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->request_id</a></td>\n";
        echo "<td class=sml nowrap><a href=\"mailto:$thisrequest->email\">$thisrequest->fullname</a></td>\n";
        echo "<td class=sml align=center>$thisrequest->date_requested</td>\n";
        echo "<td class=sml><a href=\"request.php?request_id=$thisrequest->request_id\">$thisrequest->brief";
//        if ( "$thisrequest->brief" == "" ) echo "-- no description --";
        if ( "$thisrequest->brief" == "" ) echo substr( $thisrequest->detailed, 0, 50) . "...";
        echo "</a></td>\n";
        echo "<td class=sml>&nbsp;$thisrequest->status_desc&nbsp;</td>\n";
        echo "<td class=sml>&nbsp;$thisrequest->request_type_desc&nbsp;</td>\n";
        echo "<td class=sml align=center>$thisrequest->last_change</td>\n";

        echo "</tr>\n";
      }
    }
    echo "</table>\n";
  }

} /* The end of the else ... clause waaay up there! */

include("inc/footers.php");

?>