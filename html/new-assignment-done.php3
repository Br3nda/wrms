<?php
  include( "awm-auth.php3" );
  $title = "New Assignment Entered";
  include("$homedir/apms-header.php3"); 
  include( "$funcdir/tidy-func.php3");
  include( "$funcdir/notify_emails-func.php3");

  $query = "SELECT *, employer.perorg_sort_key AS org_code FROM awm_perorg AS employer, awm_perorg AS person ";
  $query .= "WHERE person.perorg_id = $in_assigned ";
  $query .= "AND employer.perorg_id = awm_get_rel_parent( person.perorg_id, 'Employer') ";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid )
    echo "<P>Query failed:</P><PRE>$query</PRE>";
  else {
    $assignee = pg_Fetch_Object( $rid, 0 );
  }

  $query = "INSERT INTO perorg_request ( perorg_id, request_id, perreq_role ) ";
  $query .= "VALUES( $in_assigned, $in_request, 'ALLOC' )";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;New Assignment Failed!</H3>\n";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    pg_exec( "ROLLBACK;" );
    include("$homedir/apms-footer.php3");
    exit;
  }

  echo "<H2>Assignment of $assignee->perorg_name ($assignee->org_code) to WR #$in_request</H2>";

  $query = "INSERT INTO perorg_request ( perorg_id, request_id, perreq_role ) ";
  $query .= "VALUES( $in_assigned, $in_request, 'INTRST' )";

  if ( isset($was) ) error_reporting($was);
  $was = error_reporting(0);                       /* turn off error reporting - we don't care if it fails */
  $rid = pg_exec( $dbid, $query );
  error_reporting($was);                              /* show errors again */


  $mail_to = notify_emails( $dbid, $in_request );
  $msg = "Request $in_request has now been assigned to $assignee->perorg_name ($assignee->org_code). For more information visit:\n";
  $msg .= "  $wrms_home/view-request.php3?request_id=$in_request\n\n\n";
  $msub = "WR #$in_request" . "[$usr->username] Assigned to $in_assigned";

  mail( $mail_to, $msub, $msg, "From: catalyst-wrms@cat-it.co.nz\nReply-To: $rusr->email" );


  include("$homedir/apms-footer.php3");

?>



