<?php
  /*
   * This is a pretty crucial part of the system, and yet one that doesn't get seen a lot
   * by users in the normal course of events.  For this reason we have gone overboard
   * a little about reporting errors, and reporting them directly to the WRMS administrator
   * because they should be being fixed right away!.
   */

  /* This should be the only place where we connect as 'register' */
  $dbid = pg_Connect("dbname=wrms user=general");

  /* Who do we send those error messages / requests for registration to?  */
  $query = "SELECT po_data_value AS email FROM work_system, awm_usr, awm_perorg_data ";
  $query .= " WHERE system_code = 'WRMS' AND awm_usr.username = notify_usr ";
  $query .= " AND awm_perorg_data.perorg_id = awm_usr.perorg_id AND po_data_name = 'email' ";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid || ! pg_NumRows($rid)) {
    $msg = "Failed to notify WRMS system owner - register-done.php3\n";
    $msg .= "User: $in_username\n";
    $msg .= "Email: $in_email\n";
    $msg .= "Full name: $in_fullname\n";
    $msg .= "Notes: $in_note\n\n";
    $msg .= "Query: $query\n\n";
    $msg .= "Error: " + pg_ErrorMessage( $dbid );
    $admin_email = "andrew@mcmillan.net.nz";  /* desperation! */
    mail( $admin_email, "Failed registration: $in_fullname ($in_username)", $msg);
  }
  else
    $admin_email = pg_Result( $rid, 0, "email");

  /* Check for some basic errors */
  $errors = "";
  if ( ! ereg( ".+@.+\....?", "$in_email" ) )
    $errors .= "<LI>Please supply a valid e-mail address</LI>\n";
  if ( "$in_org_code" == "" && "$in_note" == "" )
    $errors .= "<LI>Please select an organisation, or enter it the details in as notes</LI>";

  if ( "$errors" != "" ) {
    include("apms-header.php3");
    echo "<H1 ALIGN=CENTER>&nbsp;<BR>WOOPS!<BR>&nbsp;</H1>";
    echo "<H3>The following problems were encountered:</H3><UL>";
    echo nl2br( $errors );
    echo "</UL><P>Please go back, correct the problems and re-submit.  Thanks.</P>";
    exit;
  }

  /* Now begin our transaction, by adding a perorg record for the person */
  $query = "INSERT INTO awm_perorg ( perorg_name, perorg_sort_key, perorg_type ) ";
  $query .= " VALUES( '$in_fullname', '$in_fullname', 'P' )";
  /* name_to_sort_key( '$in_fullname' ) */
  pg_exec($dbid, "BEGIN" );
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    pg_exec( $dbid, "ROLLBACK" );
    $msg = "Failed to create awm_perorg record - register-done.php3\n";
    $msg .= "User: $in_username\n";
    $msg .= "Email: $in_email\n";
    $msg .= "Full name: $in_fullname\n";
    $msg .= "Organisation: $in_org_code\n";
    $msg .= "Notes: $in_note\n\n";
    $msg .= "Query: $query\n\n";
    $msg .= "Error: " + pg_ErrorMessage( $dbid );
    mail( $admin_email, "Failed registration: $in_fullname ($in_username)", $msg);
    exit;
  }
  $rid = pg_exec( $dbid, "select last_value from awm_perorg_perorg_id_seq");
  $perorg_id = pg_result( $rid, 0, 0);

  $query = "INSERT INTO awm_usr ( perorg_id, validated, enabled, access_level, username, password) ";
  $query .= " VALUES( $perorg_id, 0, 0, 1000, '$in_username', '$in_password') ";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    pg_exec( $dbid, "ROLLBACK" );
    $errmsg = pg_ErrorMessage( $dbid );
    $title = "Registration Failed";
    include("apms-header.php3");
    if ( eregi( "ERROR.*Cannot insert a duplicate key into a unique index.*", $errmsg ) ) {
      echo "<P>The username \"<B>$in_username</B>\" is already in use - ";
      echo "please press the \"<B>Back</B>\" button on your browser and try again.</P>";
    }
    else {
      $msg = "Failed to create awm_usr record - register-done.php3\n";
      $msg .= "User: $in_username\n";
      $msg .= "Email: $in_email\n";
      $msg .= "Full name: $in_fullname\n";
      $msg .= "Organisation: $in_org_code\n";
      $msg .= "Notes: $in_note\n\n";
      $msg .= "Query: $query\n\n";
      $msg .= "Error: " + pg_ErrorMessage( $dbid );
      mail( $admin_email, "Failed registration: $in_fullname ($in_username)", $msg);
      echo "<H1 ALIGN=CENTER>&nbsp;<BR>WOOPS!<BR>&nbsp;</H1>";
      echo "<P>The details of an internal processing error have been notified to the administrator.</P>";
    }
    exit;
  }

  $query = "SELECT awm_set_perorg_data( $perorg_id, 'email', '$in_email') ";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    pg_exec( $dbid, "ROLLBACK" );
    $errmsg = pg_ErrorMessage( $dbid );
    $title = "Registration Failed";
    include("apms-header.php3");
    $msg = "Failed to save email address - register-done.php3\n";
    $msg .= "User: $in_username\n";
    $msg .= "Email: $in_email\n";
    $msg .= "Full name: $in_fullname\n";
    $msg .= "Organisation: $in_org_code\n";
    $msg .= "Notes: $in_note\n\n";
    $msg .= "Query: $query\n\n";
    $msg .= "Error: " + pg_ErrorMessage( $dbid );
    mail( $admin_email, "Failed registration: $in_fullname ($in_username)", $msg);
    echo "<H1 ALIGN=CENTER>&nbsp;<BR>WOOPS!<BR>&nbsp;</H1>";
    echo "<P>The details of an internal processing error have been notified to the administrator.</P>";
    exit;
  }

  $query = "INSERT INTO awm_perorg_rel (perorg_id, perorg_rel_id, perorg_rel_type) ";
  $query .= " VALUES( $in_org_code, $perorg_id, 'Employer' ) ";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    pg_exec( $dbid, "ROLLBACK" );
    $errmsg = pg_ErrorMessage( $dbid );
    $title = "Registration Failed";
    include("apms-header.php3");
    $msg = "Failed to associate with organisation - register-done.php3\n";
    $msg .= "User: $in_username\n";
    $msg .= "Email: $in_email\n";
    $msg .= "Full name: $in_fullname\n";
    $msg .= "Organisation: $in_org_code\n";
    $msg .= "Notes: $in_note\n\n";
    $msg .= "Query: $query\n\n";
    $msg .= "Error: " + pg_ErrorMessage( $dbid );
    mail( $admin_email, "Failed registration: $in_fullname ($in_username)", $msg);
    echo "<H1 ALIGN=CENTER>&nbsp;<BR>WOOPS!<BR>&nbsp;</H1>";
    echo "<P>The details of an internal processing error have been notified to the administrator.</P>";
    exit;
  }


  mail( $admin_email, "Registration: $in_fullname ($in_username)",
          "$in_fullname wishes to register for WRMS use\n"
        . "Full details are as follows:\n"
        . "Username: $in_username\n"
        . "E-Mail:   $in_email\n"
        . "Organisation: $in_org_code\n"
        . "Notes:    $in_note\n\n",
          "From: catalyst-wrms@cat-it.co.nz\nReply-To: $in_email" );


  pg_exec( $dbid, "COMMIT" );
  $title = "Registration Complete";
  include("apms-header.php3");

  echo "<P><FONT SIZE=+1>Thanks for registering to use the Catalyst work-request management system!  A notification e-mail has been sent ";
  echo "to &quot;" . htmlspecialchars($admin_email) . "&quot;.</FONT> asking for your account to be enabled.</P>";
?>

<P>We look forward to helping you.</P>

</BODY>
</HTML>
