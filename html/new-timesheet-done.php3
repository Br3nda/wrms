<?php
  include( "awm-auth.php3" );
  $title = "New Timesheet Entered";
  include("$homedir/apms-header.php3"); 
  include( "$funcdir/tidy-func.php3");

  $query = "SELECT *, employer.perorg_sort_key AS org_code FROM awm_perorg AS employer, awm_perorg AS person ";
  $query .= "WHERE person.perorg_id = $in_work_by ";
  $query .= "AND employer.perorg_id = awm_get_rel_parent( person.perorg_id, 'Employer') ";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<P>Query failed:</P><PRE>$query</PRE>";
    include("$homedir/apms-footer.php3");
    exit;
  }
  else {
    $worker = pg_Fetch_Object( $rid, 0 );
  }

  $query = "INSERT INTO request_timesheet ( request_id, work_on, work_duration, work_by, work_description, work_by_id ) ";
  $query .= "VALUES( $in_request, '$in_work_on', '$in_duration', '$worker->perorg_name', '";
  $query .= tidy( $in_description);
  $query .= "', $worker->perorg_id )";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;New Timesheet Failed!</H3>\n";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    pg_exec( "ROLLBACK;" );
    include("$homedir/apms-footer.php3");
    exit;
  }

  echo "<H2>Timesheet for $worker->perorg_name ($worker->org_code) for $in_work_duration against WR#$in_request</H2>";


/*  Don't send e-mails! 

  include( "$funcdir/notify_emails-func.php3");

  $mail_to = notify_emails( $dbid, $in_request );
  $msg = "Timesheet added for $in_work_by for $in_work_duration against WR#$in_request.";
  $msg .= "  $wrms_home/modify-request.php3?request_id=$in_request\n\n\n";
  $msub = "WR #$in_request" . "[$usr->username] Assigned to $in_assigned";

  mail( $mail_to, $msub, $msg, "From: catalyst-wrms@cat-it.co.nz\nReply-To: $rusr->email" );
*/

  include("$homedir/apms-footer.php3");

?>



