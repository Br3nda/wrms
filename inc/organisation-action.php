<?php
  $query = "BEGIN; ";
  $org_name = tidy( $org_name );
  if ( "$active" == "" ) $active = "FALSE";
  if ( "$M" == "add" ) {
    $query .= "select nextval('organisation_org_code_seq');";
    $rid = pg_Exec( $wrms_db, $query );
    if ( !$rid ) {
      echo "<P>Error with query</P><P>$query</P>";
      return;
    }
    $org_code = pg_Result( $rid, 0, 0);
    $query = "INSERT INTO organisation ( org_code, debtor_no, org_name, work_rate, active )";
    $query .= " VALUES( $org_code, '$debtor_no', '$org_name', '$work_rate', '$active' );";
  }
  else {
    $query .= "UPDATE organisation ";
    $query .= "SET debtor_no='$debtor_no', ";
    $query .= " org_name='$org_name', ";
    $query .= " debtor_no='$debtor_no', ";
    $query .= " work_rate='$work_rate', ";
    $query .= " active='$active' ";
    $query .= " WHERE org_code='$org_code' ";
  }
  $rid = pg_Exec( $wrms_db, $query );
  if ( !$rid ) {
    echo "<P>Error with query</P><P>$query</P>";
    return;
  }

  if ( isset($newSystem) ) {
    $query = "DELETE FROM org_system WHERE org_code='$org_code'";
    $rid = pg_Exec( $wrms_db, $query );
    if ( !$rid ) {
      echo "<P>Error with query</P><P>$query</P>";
      $rid = pg_Exec( $wrms_db, "ROLLBACK" );
      return;
    }
    while ( list( $k, $v ) = each( $newSystem ) ) {
      $query = "INSERT INTO org_system (org_code, system_code) VALUES( '$org_code', '$k' )";
      $rid = pg_Exec( $wrms_db, $query );
      if ( !$rid ) {
        echo "<P>Error with query</P><P>$query</P>";
        $rid = pg_Exec( $wrms_db, "ROLLBACK" );
        return;
      }
    }
  }

  $rid = pg_Exec( $wrms_db, "COMMIT" );

  $because .= "<H2>Organisation Details Changed</H2>";
?>

