<?php
  $query = "BEGIN; ";
  $t_org_name = tidy( $org_name );
  $t_abbreviation = tidy( $abbreviation );
  if ( "$active" == "" ) $active = "FALSE";
  if ( "$M" == "add" ) {
    $query .= "select nextval('organisation_org_code_seq');";
    $rid = awm_pgexec( $wrms_db, $query );
    if ( !$rid ) {
      echo "<P>Error with query</P><P>$query</P>";
      return;
    }
    $org_code = pg_Result( $rid, 0, 0);
    $query = "INSERT INTO organisation ( org_code, debtor_no, org_name, work_rate, abbreviation, active )";
    $query .= " VALUES( $org_code, '$debtor_no', '$t_org_name', '$work_rate', '$t_abbreviation', '$active' );";
  }
  else {
    $query .= "UPDATE organisation ";
    $query .= "SET org_name='$t_org_name', ";
    if ( is_member_of('Admin','Support') ) {
      if ( isset($debtor_no) ) $query .= " debtor_no=" . intval($debtor_no) . ", ";
      if ( isset($current_sla) ) $query .= " current_sla=" . ("$current_sla" == "t" ? "TRUE" : "FALSE") . ", ";
      if ( isset($work_rate) ) $query .= " work_rate='$work_rate', ";
      if ( isset($active) ) $query .= " active='$active', ";
    }
    $query .= " abbreviation='$t_abbreviation' ";
    $query .= " WHERE org_code='$org_code' ";
  }
  $rid = awm_pgexec( $wrms_db, $query );
  if ( !$rid ) return;

  if ( isset($newSystem) ) {
    $query = "DELETE FROM org_system WHERE org_code='$org_code'";
    $rid = awm_pgexec( $wrms_db, $query );
    if ( !$rid ) return;
    while ( list( $k, $v ) = each( $newSystem ) ) {
      $query = "INSERT INTO org_system (org_code, system_code) VALUES( '$org_code', '$k' )";
      $rid = awm_pgexec( $wrms_db, $query );
      if ( !$rid ) {
        echo "<P>Error with query</P><P>$query</P>";
        $rid = awm_pgexec( $wrms_db, "ROLLBACK" );
        return;
      }
    }
  }

  $rid = awm_pgexec( $wrms_db, "COMMIT" );

  $because .= "<H2>Organisation Details Changed</H2>";
?>

