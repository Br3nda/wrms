<?php
  include( "awm-auth.php3" );
  $title = "Create New Quote";
  include("$homedir/apms-header.php3"); 

  require( "$funcdir/parameters-func.php3");
  $args = parse_parameters($argv[0]);
  if ( !isset($request_id) ) $request_id = "$args->request_id";
  if ( isset($request_id) ) $current["$request_id"] = 1; else $current = "";
  include("$funcdir/active-request-list.php3");
  $current = "";
  include("$funcdir/lookup_list-func.php3");
?>

<FORM ACTION="new-quote-done.php3" METHOD=POST>
<TABLE BORDER=1 ALIGN=CENTER WIDTH=100%>
<TR>
  <TH ALIGN=RIGHT>Work request:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_request"><?php echo "$request_list"; ?></SELECT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Quote Type:</TH>
  <TD ALIGN=LEFT>&nbsp;<SELECT NAME="in_quote_type"><?php echo lookup_list( $dbid, "request_quote", "quote_type"); ?></SELECT></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Brief:</TH>
  <TD ALIGN=LEFT>&nbsp;<INPUT TYPE=text SIZE=68 NAME="in_brief" VALUE=""></TD>
</TR>

<TR VALIGN=TOP>
  <TH ALIGN=RIGHT>Details:</TH>
  <TD ALIGN=LEFT>&nbsp;<TEXTAREA NAME="in_details" ROWS=12 COLS=66  WRAP="SOFT"></TEXTAREA></TD>
</TR>

<TR>
  <TH ALIGN=RIGHT>Amount:</TH>
  <TD ALIGN=LEFT>
    &nbsp;<INPUT TYPE="text" SIZE=15 MAXLENGTH=25 NAME="in_amount" VALUE="">
    &nbsp;<SELECT NAME="in_quote_units"><?php echo lookup_list( $dbid, "request_quote", "quote_units"); ?></SELECT>
  </TD>
</TR>

<TR>
  <TD COLSPAN=2 ALIGN=CENTER><B><INPUT TYPE="submit" NAME="Submit" VALUE=" Submit "></B></TD>
</TR>

</TABLE>
</FORM>

<?php include("apms-footer.php3"); ?>
