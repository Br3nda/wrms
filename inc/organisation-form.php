<?php
  include("$base_dir/inc/code-list.php");
  $system_codes = get_code_list( "request", "system_code" );
  $courses = get_code_list( "request", "course_code" );
  $training = get_code_list( "request", "training_code" );

  include("$base_dir/inc/system-list.php");
  $system_codes = get_system_list("ECRS", "$request->system_code");
?>
<P class=helptext>Use this form to maintain organisations who may have requests associated
with them.</P>
<FORM METHOD=POST ACTION="form.php?form=<?php echo "$form"; ?>" ENCTYPE="multipart/form-data">

<TABLE WIDTH=100% cellspacing=0 border=0>

<TR><TD COLSPAN=2>&nbsp;</TD></TR>
<TR><TD class=h3 COLSPAN=2 ALIGN=RIGHT><FONT SIZE=+1><B>Organisation Details</B></FONT></TD></TR>

<TR><TH ALIGN=RIGHT>Export type:</TH><TD CLASS=mand><SELECT NAME=freport><?php echo $reports; ?></SELECT></TD></TR>

<TR><TD class=mand COLSPAN=2 ALIGN=CENTER><FONT SIZE=+1><B><INPUT TYPE=submit VALUE="Submit" NAME=submit></B></FONT></TD></TR>

</TABLE>
</FORM>

