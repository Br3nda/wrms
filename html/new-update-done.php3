<?php
  include( "awm-auth.php3" );
  $title = "New Update Submitted";
  include("$homedir/apms-header.php3"); 
  include( "$funcdir/tidy-func.php3");
  include("$funcdir/notify_emails-func.php3");

/* the following refer to the uploaded file...
  $in_file - The temporary filename in which the uploaded file was stored on the server machine. 
                - note that this is 'safe' as it is handled by Apache, rather than by the user.
  $in_file_name - The original name of the file on the sender's system. (which we ignore, for security reasons)
  $in_file_size - The size of the uploaded file in bytes. 
  $in_file_type - The mime type of the file if the browser provided this information. An example would be "image/gif". 

Note that the "$in_file" part of the above variables is whatever the NAME of the INPUT field of TYPE=file is in
the upload form. In the above upload form example, we chose to call it "in_file". 

Files will by default be stored in the server's default temporary directory. This can be changed by setting the
environment variable TMPDIR in the environment in which PHP runs. Setting it using a PutEnv() call from
within a PHP script will not work though. 

*/
  if ( isset($was) ) error_reporting($was);

  $query = "INSERT INTO system_update (update_by, update_brief, update_description, file_url, system_code, update_by_id) ";
  $query .= "VALUES( '$usr->username', '" . tidy($in_brief) . "', '" . tidy($in_description) . "', '$in_file', '$in_system', $usr->perorg_id)";
  pg_exec( $dbid, "BEGIN;" );
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;New Update Failed!</H3>\n";
    echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
    echo "<P>The failed query was:</P><TT>$query</TT>";
    pg_exec( $dbid, "ROLLBACK;" );
    include("$homedir/apms-footer.php3");
    exit;
  }

  $rid = pg_exec( "SELECT last_value FROM system_update_update_id_seq;" );
  $update_id = pg_Result( $rid,  0, 0);

  $rid = pg_exec( "SELECT * FROM system_update WHERE system_update.update_id = $update_id;" );
  $system_update = pg_Fetch_Object( $rid, 0);

  echo "<P>Your new update is number $update_id.</P>";

  /* Now that we know the number, we can move the update file */
  $file_url = "$update_dir/update-$update_id.zip";
  $sys_command = "/bin/mv $in_file $file_url";
  system( $sys_command, $retval );
  if ( $retval ) {
    echo "<H3>&nbsp;Move of New Update Failed!</H3>\n";
    echo "<P>The error code from '$sys_command' was $retval";
    pg_exec( $dbid, "ROLLBACK;" );
    include("$homedir/apms-footer.php3");
    exit;
  }

  $sys_command = "/bin/chown $usr->username $file_url";
  system( $sys_command, $retval );
  if ( $retval && $usr->access_level > 998 ) {
    /* well, we tried to... */
    echo "<H3>&nbsp;Warning: change of ownership of New Update Failed!</H3>\n";
    echo "<P>The error code from '$sys_command' was $retval";
  }


  $file_url = "$wrms_home/$file_url";
  /* now re-update the record with the final file location (not a URL actually - might need to work out the URL) */
  $query = "UPDATE system_update SET file_url = '$file_url' WHERE update_id = $update_id";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid ) {
    echo "<H3>&nbsp;Error Updating New Record!</H3>\n";
    echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
    echo "<P>The failed query was:</P><TT>$query</TT>";
    pg_exec( "ROLLBACK;" );
    include("$homedir/apms-footer.php3");
    exit;
  }

  $send_to = "";
  while( list( $key, $request_id ) = each( $in_requests ) ) {
    $query = "INSERT INTO request_update ( request_id, update_id ) VALUES( $request_id,  $update_id)";
    $rid = pg_Exec( $dbid, $query);
    if ( ! $rid ) {
      echo "<H3>&nbsp;UPDATE system_update Failed!</H3>\n";
      echo "<P>The error returned was:</P><TT>" . pg_ErrorMessage( $dbid ) . "</TT>";
      echo "<P>The failed query was:</P><TT>$query</TT>";
      pg_exec( $dbid, "ROLLBACK;" );
      include("apms-footer.php3");
      exit;
    }
    $this_list = notify_emails( $dbid, $request_id );
    if ("$this_list" != "" && "$send_to" != "" ) $send_to .= ", ";
    $send_to .= "$this_list";
  }

  pg_exec( $dbid, "END;" );

  echo "<H3>System Update added</H3>";
  $msg = "An update is now available for some work requests which ";
  $msg .= "you have indicated an interest in. For more information, ";
  $msg .= "or to download the update, visit:\n";
  $msg .= "    $wrms_home/view-update.php3?update_id=$update_id\n\n";
  $msg .= "Update $update_id - $in_brief\n\n";
  $msg .= "Description:\n " . stripslashes($in_description) . "\n";
  $msub = "Update #$update_id" . "[$usr->username] New System Update Available";

  mail( $send_to, $msub, $msg, "From: catalyst-wrms@cat-it.co.nz\nReply-To: $rusr->email" );

  include("$homedir/apms-footer.php3");
?>



