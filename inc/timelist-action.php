<?php

  if ( $logged_on && isset( $chg_on ) && is_array( $chg_on ) && is_array( $chg_amt ) ) {
    $because = "<TABLE BORDER=1 WIDTH=50% ALIGN=CENTER>";
    $query = "";
    while( list( $k, $v ) = each ( $chg_on ) ) {
      $amount = doubleval( $chg_amt[$k] );
      $invoice = doubleval( $chg_inv[$k] );
      if ( $amount == 0 ) continue;
      $because .= "<tr><td>$k</td><td>$v</td></tr>";
      $query .= "UPDATE request_timesheet SET";
      $query .= " work_charged='$v',";
      $query .= " charged_by_id=$session->user_no,";
      $query .= " charged_details='$invoice', ";
      $query .= " charged_amount=$amount";
      $query .= " WHERE timesheet_id=$k;";
    }
    if ( "$query" == "" ) return;
    $because .= "</TABLE>";

    $because .= "<TT>$query</TT>";
    $rid = pg_Exec( $wrms_db, $query );
    if ( ! $rid ) error_log( "wrms: Query Error: $query", 0);

    $msg = "<HEAD><TITLE>Timesheets Charged</TITLE></HEAD><BODY BGCOLOR=#E7FFE7><H2>Timesheets Charged**</H2>$because</BODY></HTML>";
    $msg = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\"><HTML>$msg";

    $headers = "Content-Type: text/html; charset=us-ascii";
    if ( strpos("$session->email", "@") ) $headers .= "\nFrom: $session->email";

    mail( "wrmsadmin@catalyst.net.nz", "Timesheets Charged", $msg, $headers  );

  }
?>

