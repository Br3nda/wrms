<?php
  include( "awm-auth.php3" );
  $title = "Assign Work";
  include("$homedir/apms-header.php3"); 

  require( "$funcdir/parameters-func.php3");
  $args = parse_parameters($argv[0]);
  if ( !isset($request_id) ) $request_id = "$args->request_id";
  if ( isset($request_id) ) $current["$request_id"] = 1; else $current = "";
  include("$funcdir/active-request-list.php3");

  $current = "";
  include("$funcdir/lookup_list-func.php3");
  include("$funcdir/support_user-list.php3");

?>

<FORM ACTION="new-assignment-done.php3" METHOD=POST>
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT>Work request:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_request"><?php echo "$request_list"; ?></SELECT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Assign to:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_assigned"><?php echo "$support_usr_list"; ?></SELECT></TD>
</TR>

<TR>
  <TD COLSPAN=2 ALIGN=CENTER><B><INPUT TYPE="submit" NAME="Submit" VALUE=" Submit "></B></TD>
</TR>

</TABLE>
</FORM>

<?php include("apms-footer.php3"); ?>
