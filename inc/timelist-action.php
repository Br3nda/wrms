<?php

  if ( $logged_on && isset( $chg_on ) && is_array( $chg_on ) && is_array( $chg_amt ) ) {
    $because = "<TABLE BORDER=1 WIDTH=80% ALIGN=CENTER>\n";
    $because .= "<tr><th>Timesheet</th><th>Date</th><th>Invoice</th><th align=right>Amount</th></tr>\n";
    $query = "";
    while( list( $k, $v ) = each ( $chg_on ) ) {
      $charge_ok = ( $chg_ok[$k] == 1 ? "TRUE" : "FALSE" );
      $amount = doubleval( $chg_amt[$k] );
      $invoice = $chg_inv[$k];
      $query .= "UPDATE request_timesheet SET";
      if ( $amount <> 0 ) {
        $query .= " work_charged='$v',";
        $query .= " charged_by_id=$session->user_no,";
        $query .= " charged_details='$invoice', ";
        $query .= " charged_amount=$amount, ";
        $because .= "<tr>\n<td align=center>$k</td>\n";
        $because .= "<td>$v</td>\n";
        $because .= "<td align=center>$invoice</td>\n";
        $because .= "<td align=right>" . sprintf( "%.2f", $amount) . "</td>\n";
        $because .= "</tr>\n";
      }
      $query .= " ok_to_charge=$charge_ok ";
      $query .= " WHERE timesheet_id=$k;\n";
    }
    $because .= "</TABLE>\n";

    $because .= "\n<TT>$query</TT>";
    $rid = awm_pgexec( $wrms_db, $query );

    $msg = "<HEAD>\n<TITLE>Timesheets Charged</TITLE>\n</HEAD>\n";
    $msg .= "<BODY BGCOLOR=#E7FFE7>\n";
    $msg .= "<H2>Timesheets Charged by $session->fullname</H2>$because\n</BODY>\n</HTML>";
    $msg = "<!doctype html public \"-//w3c//dtd html 4.0 transitional//en\"><HTML>$msg";

    $headers = "Content-Type: text/html; charset=us-ascii";
    if ( strpos("$session->email", "@") ) $headers .= "\nFrom: $session->email";

    mail( "wrmsadmin@catalyst.net.nz", "Timesheets Charged", $msg, $headers  );

  }
?>

