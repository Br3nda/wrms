<?php
  include( "awm-auth.php3" );
  $title = "System Update Modified";
  include("$homedir/apms-header.php3");
  include("$funcdir/tidy-func.php3");

  $query = "SELECT * FROM system_update, usr, work_system ";
  $query .= "WHERE system_update.update_id = '$update_id'";
  $query .= " AND system_update.update_by = usr.username";
  $query .= " AND system_update.system_code = work_system.system_code";
  $rid = pg_Exec( $dbid, $query );
  if ( $rid ) $rows = pg_NumRows($rid);
  if ( ! $rid || ! $rows ) {
    echo "<H3>&nbsp;Query Error - update #$update_id not found in database!</H3>";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$query</PRE>";
    include("apms-footer.php3");
    exit;
  }
  $update = pg_Fetch_Object( $rid, 0 );

  /* System Admin of request only if admin for system (funny that!)*/
  $sysadm = !strcmp( $update->admin_usr, $usr->username );

  /* Current update is editable if the user requested it or user is administrator */
  $editable = !strcmp( $update->username, $usr->username );
  if ( ! $editable ) $editable = $sysadm;

  if ( ! $editable ) {
    echo "<H2>Not Authorised</H2>";
    echo "<P>You are not authorised to change details of update #$update_id</P>";
    include("apms-footer.php3");
    exit;
  }

  $new_description = tidy( $new_description );
  $new_update_brief = tidy( $new_update_brief );
  $new_file_url = tidy( $new_file_url);

  /* scope a transaction to the whole change */
  pg_exec( "BEGIN;" );

  $mquery = "UPDATE system_update SET update_brief = '$new_update_brief', update_description = '$new_description', file_url = '$new_file_url', update_by = '$new_update_by', system_code = '$new_system' ";
  $mquery .= "WHERE system_update.update_id = $update_id";
  $rid = pg_exec( $dbid, $mquery );
  if ( ! $rid ) {
    echo "<H3>&nbsp;UPDATE system_update Failed!</H3>\n";
    echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
    echo "<P>The failed query was:</P><PRE>$mquery</PRE>";
    pg_exec( "ROLLBACK;" );
    include("apms-footer.php3");
    exit;
  }

  $query = "SELECT * FROM request_update, request WHERE request_update.update_id = $update_id AND request.request_id = request_update.request_id AND request.active ";
  $active_requests = pg_Exec( $dbid, $query);
  for ( $i = 0; $i < pg_NumRows($active_requests); $i++ ) {
    $active = pg_Fetch_Object( $active_requests, $i );
    $query = "DELETE FROM request_update WHERE update_id = $active->update_id AND request_id = $active->request_id";
    $rid = pg_Exec( $dbid, $query);
    if ( ! $rid ) {
      echo "<H3>&nbsp;UPDATE system_update Failed!</H3>\n";
      echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
      echo "<P>The failed query was:</P><PRE>$query</PRE>";
      pg_exec( "ROLLBACK;" );
      include("apms-footer.php3");
      exit;
    }
  }

  while( is_array($new_requests) && list( $key, $request_id ) = each( $new_requests ) ) {
    $query = "SELECT * FROM request_update WHERE request_update.request_id = $request_id AND request_update.update_id = $update_id";
    $rid = pg_Exec( $dbid, $query);
    if ( ! $rid ) {
      echo "<H3>&nbsp;UPDATE system_update Failed!</H3>\n";
      echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
      echo "<P>The failed query was:</P><PRE>$query</PRE>";
      pg_exec( "ROLLBACK;" );
      include("apms-footer.php3");
      exit;
    }
    if ( pg_NumRows($rid) == 0 ) {
      $query = "INSERT INTO request_update ( request_id, update_id ) VALUES( $request_id,  $update_id)";
      $rid = pg_Exec( $dbid, $query);
      if ( ! $rid ) {
        echo "<H3>&nbsp;UPDATE system_update Failed!</H3>\n";
        echo "<P>The error returned was:</P><PRE>" . pg_ErrorMessage( $dbid ) . "</PRE>";
        echo "<P>The failed query was:</P><PRE>$query</PRE>";
        pg_exec( "ROLLBACK;" );
        include("apms-footer.php3");
        exit;
      }
    }
  }

  pg_exec( "END;" );

  echo "<H2>Modification Successful</H2>";
  echo "<P>Update number $update_id has been modified.</P>";
  echo "<P>The successful query was:</P><P><FONT SIZE=-1>$mquery</FONT></P>";

  include("$homedir/apms-footer.php3"); 
?>
