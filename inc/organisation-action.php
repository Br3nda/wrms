<?php
  $query = "BEGIN; UPDATE organisation ";
  $query .= "SET debtor_no='$debtor_no', ";
  $query .= " org_name='$org_name', ";
  $query .= " debtor_no='$debtor_no', ";
  $query .= " work_rate='$work_rate', ";
  $query .= " active='$active' ";
  $query .= " WHERE org_code='$org_code' ";
  $rid = pg_Exec( $wrms_db, $query );
  if ( !$rid ) {
    echo "<P>Error with query</P><P>$query</P>";
    exit;
  }

  if ( isset($newSystem) ) {
    $query = "DELETE FROM org_system WHERE org_code='$org_code'";
    $rid = pg_Exec( $wrms_db, $query );
    if ( !$rid ) {
      echo "<P>Error with query</P><P>$query</P>";
      $rid = pg_Exec( $wrms_db, "ROLLBACK" );
      exit;
    }
    while ( list( $k, $v ) = each( $newSystem ) ) {
      $query = "INSERT INTO org_system (org_code, system_code) VALUES( '$org_code', '$k' )";
      $rid = pg_Exec( $wrms_db, $query );
      if ( !$rid ) {
        echo "<P>Error with query</P><P>$query</P>";
        $rid = pg_Exec( $wrms_db, "ROLLBACK" );
        exit;
      }
    }
  }

  $rid = pg_Exec( $wrms_db, "COMMIT" );

  $because .= "<H2>Organisation Details Changed</H2>";
?>

