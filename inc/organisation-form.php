<?php
  if ( !isset($org_code) ) $org_code = $session->org_code;
  if ( isset($org_code) && $org_code > 0 ) {

    $query = "SELECT * FROM organisation WHERE org_code='$org_code' ";
    $rid = pg_Exec( $wrms_db, $query);
    if ( ! $rid ) {
      $error_loc = "inc/organisation-form.php";
      $error_qry = "$query";
      include("inc/error.php");
      exit;
    }
    $org = pg_Fetch_Object( $rid, 0 );
  }

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    // Pre-build the list of systems
    if ( "$error_qry" == "" ) {
      $query = "SELECT * FROM work_system";
      $sys_res = pg_Exec( $wrms_db, $query );
      if ( ! $sys_res ) {
        $error_loc = "inc/organisation-form.php";
        $error_qry = "$query";
        include( "inc/error.php" );
        exit;
      }
    }
  }

  $query = "SELECT system_code FROM org_system WHERE org_code='$org_code' ";
  $result = pg_Exec( $wrms_db, $query );
  if ( ! $result ) {
    $error_loc = "inc/organisation-form.php";
    $error_qry = "$query";
    include( "inc/error.php" );
    exit;
  }
  else if ( pg_NumRows($result) > 0 ) {
    $OrgSystem = array();
    for( $i=0; $i<pg_NumRows($result); $i++) {
      $sys = pg_Result( $result, $i, 0 );
      $OrgSystem["$sys"] = 1;
    }
  }

  if ( "$because" != "" )
    echo $because;
  else if ( ! $plain ) {
    ?><P class=helptext>Use this form to maintain organisations who may have requests associated
with them.</P><?php
  }
?>
<FORM METHOD=POST ACTION="form.php?form=<?php echo "$form"; ?>" ENCTYPE="multipart/form-data">
<input type=hidden name=org_code value="<?php echo "$org->org_code"; ?>">
<TABLE WIDTH=100% cellspacing=0 border=0>

<TR><TD COLSPAN=2>&nbsp;</TD></TR>
<TR><TD class=h3 COLSPAN=2 ALIGN=RIGHT><FONT SIZE=+1><B>Organisation Details</B></FONT></TD></TR>

<?php if ( isset($org) )
  echo "<TR><TH ALIGN=RIGHT>Org Code:</TH><TD><h2>$org->org_code</TD></TR>";
?>
<TR><TH ALIGN=RIGHT>Name:</TH>
<TD><input type=text size=50 maxlen=50 name=org_name value="<?php echo "$org->org_name"; ?>"></TD></TR>
<TR><TH ALIGN=RIGHT>Debtor #:</TH>
<TD><input type=text name=debtor_no value="<?php echo "$org->debtor_no"; ?>"></TD></TR>
<TR><TH ALIGN=RIGHT>Active:</TH>
<TD><input type=checkbox value="t" name=active<?php if ( "$org->active" == "t" ) echo " checked"; ?>></TD></TR>

<?php
  if ( $roles[wrms][Admin] && pg_NumRows($sys_res) > 0 ) {
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
?>


<TR><TD class=mand COLSPAN=2 ALIGN=CENTER><FONT SIZE=+1><B><INPUT TYPE=submit VALUE="Submit" NAME=submit></B></FONT></TD></TR>

</TABLE>
</FORM>

