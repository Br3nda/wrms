<?php
$because = "<TABLE BORDER=1 WIDTH=50% ALIGN=CENTER>";
$because .= "<TR><TH ALIGN=RIGHT>System:</TH><TD> $fsystem_code</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>UserName:</TH><TD> $session->fullname ($session->username)</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>UserPassword:</TH><TD> *** validated ***</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>User Email:</TH><TD> $session->email</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>Change Type:</TH><TD> Training</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>Request No:</TH><TD> $frequestno</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>Family Name:</TH><TD> $fpfamily</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>First Name:</TH><TD> $fpfirst</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>Parent Education<BR>Completed</TH><TD> $ftparent_ed</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>Personal Development<BR>Training</TH><TD> $ftpersonal</TD></TR>\n";
$because .= "<TR><TH ALIGN=RIGHT>Leadership Certificate<BR>Training</TH><TD> $ftleadership</TD></TR>\n";
$because .= "</TABLE>";


  $msg = "<HEAD><TITLE>Request Training</TITLE></HEAD><BODY BGCOLOR=#E7FFE7><H2>Request Training</H2>$because</BODY></HTML>";
  $msg = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\"><HTML>$msg";

  $headers = "";
  if ( strpos("$session->email", "@") ) $headers = "From: $session->email";
  $headers .= "\nContent-Type: text/html; charset=us-ascii";
?>

