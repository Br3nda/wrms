<?php
  /* Insert a blank-ish record to make sure we are updating later */
  $query = "INSERT INTO list_request  ( username, list_type ) VALUES( '$session->username' , '$freport' ) ";
  $was = error_reporting(1);
  $rid = pg_Exec( $wrms_db, $query );
//  if ( !$rid ) echo "<p>$query</p>";
  error_reporting($was);

  $query = "UPDATE list_request  SET system_code='$fsystem_code', requested='now', ";
  $query .= " email='$session->email', requests=(requests+1) ";
  $query .= " WHERE username='$session->username' AND list_type='$freport' ";
  $rid = pg_Exec( $wrms_db, $query );
  if ( !$rid ) {
    echo "<P>Error with query</P><P>$query</P>";
    $msg = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\">";
    $msg .= "<HTML><HEAD><TITLE>Error with query</TITLE></HEAD><BODY>";
    $msg .= "<P>Error with query</P><P>$query</P>";
    $msg .= "<P>Message: " . pg_errormessage($wrms_db) . "</P>";
    $msg .= "</HTML>";
    mail( "andrew@cat-it.co.nz", "Error with query", $msg, "Content-Type: text/html; charset=us-ascii" );
    echo "<H2>E-Mail has been sent to the system maintainer</H2>";
    exit;
  }


  $because = "<TABLE BORDER=1 WIDTH=60% ALIGN=CENTER>";
  $because .= "<TR><TH ALIGN=RIGHT>System:</TH><TD> $fsystem_code</TD></TR>\n";
  $because .= "<TR><TH ALIGN=RIGHT>UserName:</TH><TD> $session->username</TD></TR>\n";
  $because .= "<TR><TH ALIGN=RIGHT>UserPassword:</TH><TD> *** validated ***</TD></TR>\n";
  $because .= "<TR><TH ALIGN=RIGHT>User Email:</TH><TD> $session->email</TD></TR>\n";
  $because .= "<TR><TH ALIGN=RIGHT>List Type:</TH><TD> $freport</TD></TR>\n";
  $because .= "</TABLE>";

  $msg = "<HEAD><TITLE>List Request</TITLE></HEAD><BODY BGCOLOR=#E7E7FF><H2>List Request</H2>$because</BODY></HTML>";
  $msg = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\"><HTML>$msg";

  $headers = "";
  if ( strpos("$session->email", "@") ) $headers = "From: $session->email";
  $headers .= "\nContent-Type: text/html; charset=us-ascii";

  mail( "wrmsadmin@catalyst.net.nz", "List Request Submitted", $because, $header );

  $because .= "<HR><H2>Request Sent</H2>";
?>

