<?php

function get_admin_email( $dbid, $system_code ) {
  /* Who do we send those error messages / requests for registration to?  */
  $query = "SELECT po_data_value AS email FROM work_system, awm_usr, awm_perorg_data ";
  $query .= " WHERE system_code = '$system_code' AND awm_usr.username = notify_usr ";
  $query .= " AND awm_perorg_data.perorg_id = awm_usr.perorg_id AND po_data_name = 'email' ";
  $rid = pg_exec( $dbid, $query );
  if ( ! $rid || ! pg_NumRows($rid)) {
    $msg = "Failed to get admin email...\n";
    $msg .= "System_code: $system_code\n";
    $msg .= "Query: $query\n\n";
    $msg .= "Error: " + pg_ErrorMessage( $dbid );
    $admin_email = "andrew@mcmillan.net.nz";  /* desperation! */
    mail( $admin_email, "Failed to get admin email for --$system_code--", $msg);
  }
  else
    $admin_email = pg_Result( $rid, 0, "email");

  return $admin_email;
}

?>
