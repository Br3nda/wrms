<?php
  $query = "UPDATE organisation ";
  $query .= "SET system_code='$fsystem_code',";
  $query .= " org_name='now', ";
  $query .= " debtor_no='$debtor_no', ";
  $query .= " active='$active', ";
  $query .= " WHERE username='$session->username' AND list_type='$freport' ";
  $rid = pg_Exec( $wrms_db, $query );
  if ( !$rid ) {
    echo "<P>Error with query</P><P>$query</P>";
    exit;
  }

  $because .= "<HR><H2>Organisation Changed</H2>";
?>

