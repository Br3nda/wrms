<?php
  /*
   * This is a pretty crucial part of the system, and yet one that doesn't get seen a lot
   * by users in the normal course of events.  For this reason we have gone overboard
   * a little about reporting errors, and reporting them directly to the WRMS administrator
   * because they should be being fixed right away!.
   */
  $title = "Mailing Password";
  include("apms-header.php3");
  include( "funcs/get_admin_email-func.php3");

  $dbid = pg_Connect("dbname=wrms user=general");

  /* Who do we send those error messages / requests for registration to?  */
  $admin_email = get_admin_email( $dbid, 'WRMS' );

  /* Retrieve the e-mail for the specified user */
  $query = "SELECT *, awm_get_perorg_data( perorg_id, 'email' ) AS email ";
  $query .= " FROM awm_usr WHERE LOWER(username) = LOWER('$in_username') ";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid  || ! pg_NumRows( $rid ) ) {
    echo "<H3>User '$in_username' not found, sorry!</H3>";
    echo "<P>As a last resort you can send an e-mail to <A HREF=mailto:$admin_email>the WRMS administrator</A></p>";
    exit;
  }
  $usr = pg_Fetch_Object( $rid, 0 );

  mail( "$usr->email", "Catalyst WRMS password",
          "Username: $usr->username\n"
        . "E-Mail:   $usr->email\n"
        . "Password: $usr->password\n",
          "From: catalyst-wrms@catalyst.net.nz\nReply-To: $admin_email\nErrors-To: $admin_email" );

  echo "<UL><P><BR>&nbsp;<BR>&nbsp;<BR><FONT SIZE=+1>E-mail with your password has been sent to $usr->email</FONT></P></UL>";

?>

</BODY>
</HTML>
