<?php
  include( "awm-auth.php3" );
  $title = "New Quote Submitted";
  include("$homedir/apms-header.php3"); 
  include( "$funcdir/tidy-func.php3");
  include( "$funcdir/notify_emails-func.php3");

  if ( isset($was) ) error_reporting($was);

  $query = "INSERT INTO request_quote (quoted_by, quote_brief, quote_details, quote_type, quote_amount, quote_units, request_id, quote_by_id) ";
  $query .= "VALUES( '$usr->username', '" . tidy($in_brief) . "', '" . tidy(chop($in_details)) . "', '$in_quote_type', $in_amount, '$in_quote_units', $in_request, $usr->perorg_id )";
  pg_exec( "BEGIN;" );
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;New Quote Failed!</H3>\n";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    pg_exec( "ROLLBACK;" );
    include("$homedir/apms-footer.php3");
    exit;
  }

  $rid = pg_exec( "SELECT last_value FROM request_quote_quote_id_seq;" );
  $quote_id = pg_Result( $rid,  0, 0);

  $rid = pg_exec( "SELECT * FROM request_quote WHERE request_quote.quote_id = $quote_id;" );
  $request_quote = pg_Fetch_Object( $rid, 0);

  echo "<H2>Quote added</H2>";
  echo "<P>The new quote is number $quote_id.</P>";

  pg_exec( "END;" );

  /* we don't use 'notify_email' here because we want particular people, rather than */
  /* the ones who say they are interested in the request */
  $query = "SELECT DISTINCT po_data_value AS email ";
  $query .= "FROM request, perorg_system, awm_perorg_data ";
  $query .= "WHERE request.request_id = $in_request ";
  $query .= "AND request.system_code = perorg_system.system_code ";
  $query .= "AND persys_role = 'CLTMGR' ";
  $query .= "AND awm_perorg_data.perorg_id = perorg_system.perorg_id ";
  $query .= "AND po_data_name = 'email' ";
  $query .= "AND po_data_value != '$usr->email' ";
  $peopleq = pg_Exec( $dbid, $query);
  $to = "$usr->email" ;
  if ( ! $peopleq )
    echo "<P>Query failed:</P><P>$query</P>";
  else {
    for ( $i=0; $i<pg_NumRows($peopleq); $i++ ) {
      $interested = pg_Fetch_Object( $peopleq, 0);
      $to .= " $interested->email";
    }
  }

  $msg = "A quote has now been prepared regarding WR#$in_request which ";
  $msg .= "you are registered against. For more information visit:\n";
  $msg .= "  $wrms_home/view-request.php3?request_id=$in_request\n\n\n";
  $msg .= "$in_quote_type #$quote_id - $in_brief\n\n";
  $msg .= "Amount: $in_amount $in_quote_units\n\n";
  $msg .= "Description:\n$in_details\n";
  $msub = "WR #$in_request" . "[$usr->username] Quote prepared";

  $msg .= "\nFull details of the request, with all changes, quotes, notes and updates, can be reviewed and changed at:\n"
           . "    $wrms_home/view-request.php3?request_id=$request->request_id\n";

  mail( $mail_to, $msub, $msg, "From: catalyst-wrms@cat-it.co.nz\nReply-To: $rusr->email" );


  include("$homedir/apms-footer.php3");
?>



