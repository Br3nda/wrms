<?php
  $query = "BEGIN; ";
  $sys_desc = tidy( $sys_desc );
  if ( "$active" == "" ) $active = "FALSE";
  if ( "$M" == "add" ) {
    $system_code = $sys_code;
    $query .= "INSERT INTO work_system ( system_code, system_desc, active )";
    $query .= " VALUES( '$system_code', '$sys_desc', '$active' );";
  }
  else {
    $query .= "UPDATE work_system ";
    $query .= "SET system_desc='$sys_desc' ";
    $query .= ", active='$active' ";
    $query .= " WHERE system_code='$system_code' ";
  }
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

  $because .= "<H2>System Details ";
  if ( "$M" == "add" )
    $because .= "Added</H2>";
  else
    $because .= "Changed</H2>";
?>

