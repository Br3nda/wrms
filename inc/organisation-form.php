<?php
  if ( isset($org_code) && $org_code > 0 ) {

    $query = "SELECT * FROM organisation WHERE org_code='$org_code' ";
    $rid = pg_Exec( $wrms_db, $query);
    if ( ! $rid ) {
      $error_loc = "organisation-form.php";
      $error_qry = "$query";
      include("inc/error.php");
    }
    $org = pg_Fetch_Object( $rid, 0 );
  }
?>
<P class=helptext>Use this form to maintain organisations who may have requests associated
with them.</P>
<FORM METHOD=POST ACTION="form.php?form=<?php echo "$form"; ?>" ENCTYPE="multipart/form-data">
<input type=hidden name=org_code value="<?php echo "$org->org_code"; ?>">
<TABLE WIDTH=100% cellspacing=0 border=0>

<TR><TD COLSPAN=2>&nbsp;</TD></TR>
<TR><TD class=h3 COLSPAN=2 ALIGN=RIGHT><FONT SIZE=+1><B>Organisation Details</B></FONT></TD></TR>

<?php if ( isset($org) )
  echo "<TR><TH ALIGN=RIGHT>Org Code:</TH><TD>$org->org_code</TD></TR>";
?>
<TR><TH ALIGN=RIGHT>Name:</TH>
<TD><input type=text size=50 maxlen=50 name=org_name value="<?php echo "$org->org_name"; ?>"></TD></TR>
<TR><TH ALIGN=RIGHT>Debtor #:</TH>
<TD><input type=text name=debtor_no value="<?php echo "$org->debtor_no"; ?>"></TD></TR>
<TR><TH ALIGN=RIGHT>Active:</TH>
<TD><input type=checkbox value="t" name=active<?php if ( "$org->active" == "t" ) echo " checked"; ?>></TD></TR>

<TR><TD class=mand COLSPAN=2 ALIGN=CENTER><FONT SIZE=+1><B><INPUT TYPE=submit VALUE="Submit" NAME=submit></B></FONT></TD></TR>

</TABLE>
</FORM>

