<?php
  $query = "UPDATE organisation ";
  $query .= "SET debtor_no='$debtor_no', ";
  $query .= " org_name='$org_name', ";
  $query .= " debtor_no='$debtor_no', ";
  $query .= " active='$active' ";
  $query .= " WHERE org_code='$org_code' ";
  $rid = pg_Exec( $wrms_db, $query );
  if ( !$rid ) {
    echo "<P>Error with query</P><P>$query</P>";
    exit;
  }

  $because .= "<HR><H2>Organisation Changed</H2>";
?>

