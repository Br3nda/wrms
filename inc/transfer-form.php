<?php
  include("$base_dir/inc/system_code-list.php");
  $system_codes = get_system_code_list("HM", "$request->system_code");
  $xfrsystem_codes = get_system_code_list("*", "$request->system_code");
?>
<P class=helptext>Use this form to transfer a request from one system_code to another. 
As well as filling out this form you will need to submit a database change to change the address
and phone number details for the request.</p>
<FORM METHOD=POST ACTION="form.php?form=<?php echo "$form"; ?>" ENCTYPE="multipart/form-data">

<TABLE WIDTH=100% cellspacing=0 border=0>
<TR><TD COLSPAN=2>&nbsp;</TD></TR>
<TR><TD CLASS=h3 COLSPAN=2 ALIGN=RIGHT><FONT SIZE=+1><B>Request details</B></FONT></TD></TR>
<?php
  if ( "$system_codes" <> "" ) {
    echo "<TR><th>System:</TH><TD CLASS=mand BGCOLOR=#fff070>\n";
    echo "<select name=fsystem_code>$system_codes</select></td></tr>";
  }
?>
<TR><th>Requestship no:</TH><TD class=mand><INPUT NAME=frequestno TYPE=text SIZE=10 VALUE="<?php echo "$requestno"; ?>"></TD></TR>
<TR><th>Family Name:</TH><TD class=mand><INPUT NAME=fpfamily TYPE=text SIZE=30 VALUE="<?php echo "$request->pfamily"; ?>"></TD></TR>
<TR><th>First Name:</TH><TD class=mand><INPUT NAME=fpfirst TYPE=text SIZE=30 VALUE="<?php echo "$request->pfirst"; ?>"></TD></TR>

<TR><th>New System:</TH><TD class=mand><SELECT NAME=fnewsystem_code><?php echo $xfrsystem_codes; ?></SELECT></TD></TR>

<TR><TD COLSPAN=2>&nbsp;</TD></TR>
<TR><TD CLASS=h3 COLSPAN=2 ALIGN=RIGHT><FONT SIZE=+1><B>General Information</B></FONT></TD></TR>
<TR><th>General:<BR>&nbsp;</TH><TD><TEXTAREA NAME=fgeneral ROWS=5 COLS=60 WRAP></TEXTAREA></TD></TR>


<TR><TD CLASS=mand COLSPAN=2 ALIGN=CENTER><FONT SIZE=+1><B><INPUT TYPE=submit VALUE="Update Request" name=submit></B></FONT></TD></TR>


</TABLE>
</FORM>
</BODY>
</HTML>
