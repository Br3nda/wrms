<?php
  if ( !isset($org_code) ) $org_code = $session->org_code;
  if ( "$org_code" == "new" ) unset($org_code);
  if ( isset($org_code) && $org_code > 0 ) {
    if ( ! is_member_of('Admin','Support') ) $org_code = $session->org_code;

    $query = "SELECT * FROM organisation WHERE org_code='$org_code' ";
    $rid = awm_pgexec( $wrms_db, $query);
    if ( ! $rid ) {
      $error_loc = "inc/organisation-form.php";
      $error_qry = "$query";
      include("inc/error.php");
      exit;
    }
    $org = pg_Fetch_Object( $rid, 0 );
  }

  if ( is_member_of('Admin','Support') ) {
    // Pre-build the list of systems
    if ( "$error_qry" == "" ) {
      $query = "SELECT * FROM work_system";
      $sys_res = awm_pgexec( $wrms_db, $query );
    }
  }

  $query = "SELECT system_code FROM org_system WHERE org_code='$org_code' ";
  $result = awm_pgexec( $wrms_db, $query );
  if ( $result && pg_NumRows($result) > 0 ) {
    $OrgSystem = array();
    for( $i=0; $i<pg_NumRows($result); $i++) {
      $sys = pg_Result( $result, $i, 0 );
      $OrgSystem["$sys"] = 1;
    }
  }

  if ( "$because" != "" ) echo $because;

  echo "<FORM METHOD=POST ACTION=\"form.php?form=$form\" ENCTYPE=\"multipart/form-data\">\n";

  if ( isset($org) )
    echo "<input type=hidden name=org_code value=\"$org->org_code\">";
  else
    echo "<input type=hidden name=M value=add>";

  echo "<TABLE WIDTH=100% cellspacing=0 border=0>\n";

  echo "<TR><TD COLSPAN=2>&nbsp;</TD></TR>\n";
  echo "<TR><TD class=h3 COLSPAN=2 ALIGN=RIGHT><FONT SIZE=+1><B>Organisation Details</B></FONT></TD></TR>\n";

  if ( isset($org) ) {
    echo "<TR><TH ALIGN=RIGHT>Org Code:</TH><TD><h2>$org->org_code</TD></TR>";
  }

  echo "<TR><TH ALIGN=RIGHT>Name:</TH>\n";
  echo "<TD><input type=text size=50 maxlen=50 name=org_name value=\"$org->org_name\"></TD></TR>\n";
  echo "<TR><TH ALIGN=RIGHT>Abbreviation:</TH>\n";
  echo "<TD><input type=text size=10 maxlen=10 name=abbreviation value=\"$org->abbreviation\"></TD></TR>\n";

  if ( is_member_of('Admin','Support') ) {
    echo "<TR><TH ALIGN=RIGHT>Active:</TH>\n";
    echo "<TD><input type=checkbox value=\"t\" name=active ". ( "$org->active" == "f" ? "" : " checked")."></TD></TR>\n";
    echo "<TR><TH ALIGN=RIGHT>Current SLA:</TH>\n";
    echo "<TD><input type=checkbox value=\"t\" name=current_sla". ("$org->current_sla" == "t" ? " checked" : "") . "></TD></TR>\n";
    echo "<TR><TH ALIGN=RIGHT>Debtor #:</TH>\n";
    echo "<TD><input type=text size=5 name=debtor_no value=\"$org->debtor_no\"></TD></TR>\n";
    echo "<TR><TH ALIGN=RIGHT>Hourly&nbsp;Rate:</TH>\n";
    echo "<TD><input type=text size=5 name=work_rate value=\"$org->work_rate\"></TD></TR>\n";

    if ( $sys_res && pg_NumRows($sys_res) > 0 ) {
      // This displays checkboxes to select the organisations systems.
      echo "\n<tr><th align=right valign=top>&nbsp;<BR>Systems:</th>\n";
      echo "<td><table border=0 cellspacing=0 cellpadding=2><tr>\n";
      for ( $i=0; $i < pg_NumRows($sys_res); $i++) {
        $sys = pg_Fetch_Object( $sys_res, $i );
        if ( $i > 0 && ($i % 2) == 0 ) echo "</tr><tr>";
        echo "<td><font size=2><input type=checkbox name=\"newSystem[$sys->system_code]\"";
        if ( isset($OrgSystem) && is_array($OrgSystem) && $OrgSystem[$sys->system_code] ) echo " CHECKED";
        echo "> $sys->system_desc\n";
        echo "</font></td>\n";
      }
      echo "</tr></table></td></tr>\n";
    }
  } // if admin


echo "<TR><TD class=mand COLSPAN=2 ALIGN=CENTER><FONT SIZE=+1><B><INPUT TYPE=submit VALUE=\"Submit\" NAME=submit></B></FONT></TD></TR>\n";
echo "</TABLE>\n</FORM>\n";

?>