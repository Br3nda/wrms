<?php
  include("$base_dir/inc/html-format.php");
  include( "$base_dir/inc/user-list.php" );
function nice_time( $in_time ) {
  /* does nothing yet... */
  return substr("$in_time", 2);
}
  if ( "$because" != "" )
    echo $because;
  else if ( ! $plain ) {
//    ? ><P class=helptext>Use this form to maintain organisations who may have requests associated
// with them.</P><?php
  }
// <P class=helptext>This page lists timesheets.</P>

  echo "<form method=get action=\"$base_url/form.php\">\n";
  echo "<input type=hidden value=\"timelist\" name=form>\n";
  echo "<table border=0 cellpadding=0 cellspacing=2 align=center class=row0><tr><td><table border=0 cellpadding=0 cellspacing=0 width=100%><tr>\n";
  echo "<td class=smb>Find:</td>\n";
  printf("<td class=sml><input class=sml type=text size=\"10\" name=search_for value=\"%s\"></td>\n", htmlspecialchars($search_for));

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    if ( !isset($user_no) ) $user_no = $session->user_no;
    $user_list = "<option value=\"\">--- All Users ---</option>" . get_user_list( "Support", "", $user_no );
  }
  echo "<td class=smb align=right>&nbsp;User:</td><td class=sml><select class=sml name=user_no>$user_list</select></td>\n";

  printf("<td align=right><input type=checkbox value=1 name=uncharged%s></td><td align=left class=smb><label for=uncharged>Uncharged</label></td>\n", ("$uncharged"<>"" ? " checked" : ""));
  printf("<td align=right><input type=checkbox value=1 name=charge%s></td><td align=left class=smb><label for=charge>Charge</label></td>\n", ("$charge"<>"" ? " checked" : ""));
  echo "</tr></table></td>\n<tr><td><table border=0 cellpadding=0 cellspacing=0 width=100%>\n";
  include("inc/system-list.php");
  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] )
    $system_list = get_system_list( "", "$system_code", 35);
  else
    $system_list = get_system_list( "CES", "$system_code", 35);
  echo "<td class=smb>System:</td><td class=sml><font size=1><select class=sml name=system_code><option value=\"\">--- All Systems ---</option>$system_list</select></font></td>\n";

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    include( "inc/organisation-list.php" );
    $orglist = "<option value=\"\">--- All Organisations ---</option>\n" . get_organisation_list( "$org_code", 35 );
    echo "<td class=smb>&nbsp; &nbsp;Organisation:</td><td class=sml><select class=sml name=\"org_code\">\n$orglist</select></td>\n";
  }
  echo "<td align=left><input type=submit class=submit alt=go id=go value=\"GO>>\"name=go></td>\n";
  echo "</tr></table></td></tr></table>\n</form>\n";

  if ( "$search_for$system_code " != "" ) {
    $query = "SELECT request.*, organisation.*, request_timesheet.*, ";
    $query .= " worker.fullname AS worker_name, requester.fullname AS requester_name";
    $query .= " FROM request, usr AS worker, usr AS requester, organisation, request_timesheet ";
    $query .= " WHERE request_timesheet.request_id = request.request_id";
    $query .= " AND worker.user_no = work_by_id ";
    $query .= " AND requester.user_no = requester_id ";
    $query .= " AND organisation.org_code = requester.org_code ";

    if ( "$user_no" <> "" ) {
      $query .= " AND work_by_id=$user_no ";
    }

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
    if ( "$uncharged" != "" ) {
      if ( "$charge" != "" )
        $query .= " AND request_timesheet.ok_to_charge=TRUE ";
      $query .= " AND request_timesheet.work_charged IS NULL ";
      $query .= " ORDER BY org_code, work_on";
    }
    else {
      $query .= " ORDER BY organisation.org_code, request_timesheet.request_id, request_timesheet.work_on";
      $query .= " LIMIT 100 ";
    }
    $result = awm_pgexec( $wrms_db, $query );
    if ( $result ) {
      echo "<p><small>&nbsp;" . pg_NumRows($result) . " timesheets found\n"; // <p>$query</p>";
      if ( "$uncharged" != "" ) {
        printf( "<form enctype=\"multipart/form-data\" method=post action=\"%s%s\">\n", $REQUEST_URI, ( ! strpos( $REQUEST_URI, "uncharged" ) ? "&uncharged=1" : ""));
      }
      echo "<table border=\"0\" cellspacing=1 align=center><tr>\n";
      echo "<th class=cols>Work for</th><th class=cols>Done on</th>";
      echo "<th class=cols>Duration</th><th class=cols>Rate</th>";
      echo "<th class=cols>Done By</th>";
      if ( "$uncharged" == "" )
        echo "<th class=cols>Charged on</th>";
      echo "<th class=cols>Description</th></tr>";

      // Build table of organisations found
      for ( $i=0; $i < pg_NumRows($result); $i++ ) {
        $timesheet = pg_Fetch_Object( $result, $i );

        printf( "<tr class=row%1d>\n", ($i % 2));

        echo "<td class=sml>$timesheet->requester_name ($timesheet->abbreviation, #$timesheet->debtor_no)</td>\n";
        echo "<td class=sml nowrap>" . substr( nice_date($timesheet->work_on), 7) . "</td>\n";
        echo "<td class=sml nowrap>$timesheet->work_quantity $timesheet->work_units</td>\n";
        echo "<td class=sml align=right nowrap>$timesheet->work_rate&nbsp;</td>\n";
        echo "<td class=sml>$timesheet->worker_name</td>\n";
        if ( "$timesheet->work_charged" == "" ) {
          if ( "$uncharged" == "" ) echo "<td class=sml>uncharged</td>";
        }
        else
          echo "<td class=sml>" . substr( nice_date($timesheet->work_charged), 7) . "</td>";
        echo "<td class=sml>" . html_format( $timesheet->work_description) . " <I> <a href=\"$base_url/request.php?request_id=$timesheet->request_id\">(WR #$timesheet->request_id)</A></I></td>";

        if ( "$uncharged" != "" ) {
          echo "</tr>\n";
          printf( "<tr class=row%1d>\n", ($i % 2));
          echo "<td class=smb align=right>Request:</td>\n";
          echo "<td class=sml align=center>#$timesheet->request_id</td>\n";
          echo "<td class=sml colspan=4><a href=\"$base_url/request.php?request_id=$timesheet->request_id\">$timesheet->brief</a></td>\n";
          echo "</tr>\n";
          printf( "<tr class=row%1d>\n", ($i % 2));
          echo "<td colspan=6><table align=right border=0 cellspacing=0 cellpadding=0 width=100%><tr>\n";
          echo "<td class=sml align=right>";
          printf("<input type=\"checkbox\" value=\"1\" id=\"$timesheet->timesheet_id\" name=\"chg_ok[$timesheet->timesheet_id]\"%s>", ( "$timesheet->ok_to_charge" == "t" ? " checked" : ""));
          printf("<input type=hidden name=\"chg_worker[$timesheet->timesheet_id]\" value=\"%s\">", htmlspecialchars($timesheet->worker_name));
          printf("<input type=hidden name=\"chg_desc[$timesheet->timesheet_id]\" value=\"%s\">", htmlspecialchars($timesheet->work_description));
          printf("<input type=hidden name=\"chg_request[$timesheet->timesheet_id]\" value=\"%s\">", htmlspecialchars($timesheet->request_id));
          printf("<input type=hidden name=\"chg_requester[$timesheet->timesheet_id]\" value=\"%s\">", htmlspecialchars($timesheet->requester_name));
          echo "</td>\n";
          echo "<td class=smb valign=top><label for=\"$timesheet->timesheet_id\" class=smb>OK to Charge</label>&nbsp;</td>\n";
          echo "<td class=smb align=right>&nbsp;Invoice:</td>\n";
          echo "<td class=sml><font size=2><input type=text size=6 name=\"chg_inv[$timesheet->timesheet_id]\" value=\"\"></font>&nbsp;</td>\n";
          echo "<td class=smb align=right>&nbsp;Amount:</td>\n";
          echo "<td class=sml><font size=2><input type=text size=8 name=\"chg_amt[$timesheet->timesheet_id]\" value=\"\"></font>&nbsp;</td>\n";
          echo "<td class=smb align=right>&nbsp;Charged&nbsp;On:</td>\n";
          echo "<td class=sml><font size=2><input type=text size=10 name=\"chg_on[$timesheet->timesheet_id]\" value=\"" . date( "d/m/Y" ) . "\"></font>&nbsp;</td>\n";
          echo "</tr></table></td>\n";
        }
        echo "</tr>\n";
      }
      if ( "$uncharged" != "" ) {
        echo "<tr><td colspan=6><input type=submit class=submit alt=\"apply changes\" name=submit value=\"Apply Charges\"></td></tr>\n";
        echo "</form>\n";
      }
      echo "</table>\n";
    }
  }
?>
</TABLE>
</FORM>

