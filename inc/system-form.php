<?php
  if ( isset($system_code) && $system_code <> "" ) {

    $query = "SELECT * FROM work_system WHERE system_code='$system_code' ";
    $rid = pg_Exec( $wrms_db, $query);
    if ( ! $rid ) {
      $error_loc = "inc/system-form.php";
      $error_qry = "$query";
      include("inc/error.php");
      exit;
    }
    $sys = pg_Fetch_Object( $rid, 0 );
  }

  if ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] ) {
    // Pre-build the list of organisations
    if ( "$error_qry" == "" ) {
      $query = "SELECT * FROM organisation";
      $org_res = pg_Exec( $wrms_db, $query );
      if ( ! $org_res ) {
        $error_loc = "inc/system-form.php";
        $error_qry = "$query";
        include( "inc/error.php" );
        exit;
      }
    }
  }

  $query = "SELECT org_code FROM org_system WHERE system_code='$system_code' ";
  $result = pg_Exec( $wrms_db, $query );
  if ( ! $result ) {
    $error_loc = "inc/system-form.php";
    $error_qry = "$query";
    include( "inc/error.php" );
    exit;
  }
  else if ( pg_NumRows($result) > 0 ) {
    $OrgSystem = array();
    for( $i=0; $i<pg_NumRows($result); $i++) {
      $org = pg_Result( $result, $i, 0 );
      $OrgSystem["$org"] = 1;
    }
  }

  if ( "$because" != "" )
    echo $because;
  else if ( ! $plain ) {
    ?><P class=helptext>Use this form to maintain systems which may have requests associated
with them.</P><?php
  }
?>
<FORM METHOD=POST ACTION="form.php?form=<?php echo "$form&system_code=$system_code"; ?>" ENCTYPE="multipart/form-data">
<input type=hidden name=system_code value="<?php echo "$sys->system_code"; ?>">
<TABLE WIDTH=100% cellspacing=0 border=0>

<TR><TD COLSPAN=2>&nbsp;</TD></TR>
<TR><TD class=h3 COLSPAN=2 ALIGN=RIGHT><FONT SIZE=+1><B>System Details</B></FONT></TD></TR>
<TR><TH ALIGN=RIGHT>System Code:</TH><TD>
<?php
  if ( isset($sys) )
    echo "<h2>$sys->system_code";
  else
    echo "<input type=text size=10 maxlen=10 name=sys_code><input type=hidden name=M value=add>";
?></TD></TR>
<TR><TH ALIGN=RIGHT>Description:</TH>
<TD><input type=text size=50 maxlen=50 name=sys_desc value="<?php
 if ( isset($sys) ) echo htmlspecialchars($sys->system_desc); 
?>"></TD></TR>
<TR><TH ALIGN=RIGHT>Active:</TH>
<TD><input type=checkbox value="t" name=active<?php if ( strtolower(substr("$sys->active",0,1)) == "t" ) echo " checked"; ?>></TD></TR>

<?php
  if ( $roles[wrms][Admin] && pg_NumRows($org_res) > 0 ) {
    // This displays checkboxes to select the systems organisations.
    echo "\n<tr><th align=right valign=top>&nbsp;<BR>Organisations:</th>\n";
    echo "<td><table border=0 cellspacing=0 cellpadding=2><tr>\n";
    for ( $i=0; $i < pg_NumRows($org_res); $i++) {
      $sys = pg_Fetch_Object( $org_res, $i );
      if ( $i > 0 && ($i % 2) == 0 ) echo "</tr><tr>";
      echo "<td><font size=2><input type=checkbox name=\"newSystem[$sys->org_code]\"";
      if ( isset($OrgSystem) && is_array($OrgSystem) && $OrgSystem[$sys->org_code] ) echo " CHECKED";
      echo "> $sys->org_name\n";
      echo "</font></td>\n";
    }
    echo "</tr></table></td></tr>\n";
  }
?>


<TR><TD class=mand COLSPAN=2 ALIGN=CENTER><FONT SIZE=+1><B><INPUT TYPE=submit VALUE="Submit" NAME=submit></B></FONT></TD></TR>

</TABLE>
</FORM>

