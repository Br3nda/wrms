<?php
  include( "awm-auth.php3" );
  include("$funcdir/parameters-func.php3");
  $args = parse_parameters( $argv[0] );

  $title = "Change Details for $args->username";
  include("$homedir/apms-header.php3");
  include("$funcdir/organisation-list.php3");
  $arguser = $args["username"];
  if ( $arguser <> "" && $usr->access_level > 950 ) {
    $query = "SELECT * FROM usr WHERE username='$arguser'";
    $rid = pg_exec( $dbid, $query );
    if ( pg_NumRows($rid) == 1 ) $lusr = pg_Fetch_Object($rid,0);
  }

  if ( !isset($lusr) ) $lusr = $usr;

?>

<H2>User Details</H2>

<FORM ACTION="user-details-changed.php3" METHOD=POST>
<INPUT TYPE="hidden" NAME="username" VALUE="<?php echo $lusr->username; ?>">
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT>User Name:</TH>
  <TD ALIGN=LEFT><FONT SIZE=+2><?php echo "$lusr->username";?></FONT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Full name:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE="text" SIZE=68 NAME="new_fullname" VALUE="<?php echo $lusr->fullname; ?>"></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Password:</TH>
  <TD ALIGN=LEFT>
    &nbsp;&nbsp;Old:<INPUT TYPE="password" SIZE=20 NAME="old_password" VALUE="">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    &nbsp;New:<INPUT TYPE="password" SIZE=20 NAME="new_password" VALUE="">
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>EMail Address:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE="text" SIZE=68 NAME="new_email"  VALUE="<?php echo $lusr->email; ?>"></TD>
</TR>

<?php /**** Only if a system administrator... */
if ( $usr->access_level > 950 ) {
?>
<TR>
  <TH ALIGN=RIGHT>Enable / Validate:</TH>
  <TD ALIGN=LEFT>
     &nbsp;<LABEL><INPUT TYPE="checkbox" NAME="enabled" VALUE="1"<?php if ($lusr->enabled > 0) echo " CHECKED"; ?>> Enabled</LABEL>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
     &nbsp;<LABEL><INPUT TYPE="checkbox" NAME="validated" VALUE="1"<?php if ($lusr->validated > 0) echo " CHECKED"; ?>> Validated</LABEL>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
  </TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Organisation:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="new_organisation" ><?php echo $org_list; ?></SELECT></TD>
</TR>

<?php
}
?>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>Notes:</TH>
  <TD ALIGN=LEFT>&nbsp;<TEXTAREA NAME="new_notes" ROWS=6 COLS=66  WRAP="SOFT"><?php echo $lusr->note; ?></TEXTAREA>
  </TD>
</TR>

<TR>
  <TD COLSPAN=3 ALIGN=CENTER>
         <INPUT TYPE="submit" NAME="submit" VALUE="Submit Changes">
  </TD>
</TR>
</TABLE>

</FORM>

<?php include("apms-footer.php3"); ?>
