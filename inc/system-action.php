<?php
  $query = "BEGIN; UPDATE work_system ";
  $query .= "SET system_desc='$sys_desc' ";
//  $query .= ", active='$active' ";
  $query .= " WHERE system_code='$system_code' ";
  $rid = pg_Exec( $wrms_db, $query );
  if ( !$rid ) {
    echo "<P>Error with query</P><P>$query</P>";
    exit;
  }

  if ( isset($newSystem) ) {
    $query = "DELETE FROM org_system WHERE system_code='$system_code'";
    $rid = pg_Exec( $wrms_db, $query );
    if ( !$rid ) {
      echo "<P>Error with query</P><P>$query</P>";
      $rid = pg_Exec( $wrms_db, "ROLLBACK" );
      exit;
    }
    while ( list( $k, $v ) = each( $newSystem ) ) {
      $query = "INSERT INTO org_system (org_code, system_code) VALUES( '$k', '$system_code' )";
      $rid = pg_Exec( $wrms_db, $query );
      if ( !$rid ) {
        echo "<P>Error with query</P><P>$query</P>";
        $rid = pg_Exec( $wrms_db, "ROLLBACK" );
        exit;
      }
    }
  }

  $rid = pg_Exec( $wrms_db, "COMMIT" );

  $because .= "<H2>System Details Changed</H2>";
?>

