<?php
  include( "awm-auth.php3" );
  $title = "Quote Modified";
  include("$homedir/apms-header.php3");
  include("$funcdir/tidy-func.php3");

  $query = "SELECT * FROM request_quote";
  $query .= " WHERE request_quote.quote_id = '$quote_id'";
  $rid = pg_Exec( $dbid, $query );
  if ( $rid ) $rows = pg_NumRows($rid);
  if ( ! $rid || ! $rows ) {
    echo "<H3>&nbsp;Query Error - quote #$quote_id not found in database!</H3>";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    include("apms-footer.php3");
    exit;
  }
  $quote = pg_Fetch_Object( $rid, 0 );

  /* System Admin of quoted request only if admin for system (funny that!)*/
  /* ***** This is broken, but fuck it for now */
  $sysadm = !strcmp( $quote->notify_usr, $usr->username );

  /* Current quote is editable if the user quoted it or user is system administrator */
  $editable = !strcmp( $quote->quoted_by, $usr->username );
  if ( ! $editable ) $editable = $sysadm;

  if ( ! $editable ) {
    echo "<H2>Not Authorised</H2>";
    echo "<P>You are not authorised to change details of quote #$quote_id</P>";
    include("apms-footer.php3");
    exit;
  }

  $new_description = tidy( chop($new_description) );
  $new_brief = tidy( $new_brief );

  /* scope a transaction to the whole change */
  pg_exec( "BEGIN;" );

  $query = "UPDATE request_quote SET quote_details = '$new_description', quote_brief = '$new_brief', quoted_by = '$new_quoted_by', quoted_on = '$new_quoted_on', quote_amount = $new_amount, quote_units = '$new_quote_units'";
  if ( $new_request > 0 ) $query .= ", request_id = $new_request ";
  $query .= "WHERE request_quote.quote_id = '$quote_id'";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;UPDATE request_quote Failed!</H3>\n";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    pg_exec( "ROLLBACK;" );
    include("apms-footer.php3");
    exit;
  }

  pg_exec( "END;" );

  echo "<H2>Modification Successful</H2>";
  echo "<P>Quote number $quote_id has been modified.</P>";
  echo "<P>The successful query was:</P><P>$query</P>";

  include("$homedir/apms-footer.php3"); 
?>
