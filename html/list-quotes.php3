<?php
  include( "awm-auth.php3" );
  $title = "List Quotes";
  include("$homedir/apms-header.php3"); 
  require("$funcdir/nice_date-func.php3");
  require("$funcdir/html_format-func.php3");
?>


<TABLE BORDER=2 WIDTH=100%>
<TR><TH>Quote</TH><TH>WR #</TH><TH>Done By</TH><TH>Brief</TH><TH>Done On</TH><TH>Type</TH><TH>Amount</TH></TR>

<?php /**** data rows in the usr table... */
  $query = "SELECT *, awm_get_lookup_desc('request_quote','quote_type', request_quote.quote_type) AS type_desc FROM request_quote, request WHERE request.request_id = request_quote.request_id AND request.active ";
  $rid = pg_Exec( $dbid,$query );
  $rows = pg_NumRows( $rid );
  for ( $i=0; $i < $rows; $i++ ) {
    $quote = pg_Fetch_Object( $rid, $i );
    echo "<TR>";
    echo "<TH ALIGN=CENTER VALIGN=TOP ROWSPAN=2><FONT SIZE=+2>$quote->quote_id</FONT></TH>\n";
    echo "<TD ALIGN=CENTER><A HREF=\"view-request.php3?request_id=$quote->request_id\">$quote->request_id</A></TD>\n";
    echo "<TD ALIGN=CENTER>$quote->quoted_by</TD>\n";
    echo "<TD><A HREF=\"view-quote.php3?quote_id=$quote->quote_id\">$quote->quote_brief</A></TD>\n";
    echo "<TD ALIGN=CENTER>" . nice_date($quote->quoted_on) . "</TD>\n";
    echo "<TD ALIGN=CENTER>$quote->quote_type - $quote->type_desc</TD>\n";
    echo "<TD ALIGN=RIGHT>" . number_format($quote->quote_amount, 2) . " $quote->quote_units</TD>\n";
    echo "</TR><TR><TD COLSPAN=6>";
    echo html_format($quote->quote_details) . "</A></TD>\n";
    echo "</TR><TR><TD></TD><TD></TD></TR>\n";
  }

  echo "</TABLE>\n";

  include("$homedir/apms-footer.php3");
?>

