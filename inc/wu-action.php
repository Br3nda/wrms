<?php
$debuglevel = 7;
  $rid = awm_pgexec( $wrms_db, "BEGIN;", "wu-action", false );

  if ( eregi("update", $submit) ) {
    $query = "";
    reset($new);
    foreach ($new as $key => $value) {
      $query .= ", $key = '" . tidy($value) . "' ";
    }
    $query = substr( $query, 2);
    $query = "UPDATE wu SET $query WHERE node_id = $node_id AND wu_by = $session->user_no; ";
  }
  else {
    $fields = "";
    $values = "";
    if ( !isset($node_id) || $node_id == 0 ) {
      $query = "SELECT nextval('infonode_node_id_seq');";
      $rid = awm_pgexec( $wrms_db, $query, "wu-action", false );
      if ( $rid ) {
        $node_id = pg_Result( $rid, 0, 0);
        $query = "INSERT INTO infonode (node_id, nodename, created_by) ";
        $query .= "VALUES( $node_id, '" . tidy($nodename) . "', $session->user_no );";
        $rid = awm_pgexec( $wrms_db, $query, "wu-action", false );
      }
    }

    $new['node_id'] = $node_id;
    $new['wu_by'] = $session->user_no;
    reset($new);
    foreach ($new as $key => $value) {
      $fields .= ", $key";
      $values .= ", '" . tidy($value) . "' ";
    }
    $fields = substr( $fields, 2);
    $values = substr( $values, 2);
    $query = "INSERT INTO wu ($fields) VALUES($values); ";
  }
  $rid = awm_pgexec( $wrms_db, $query, "wu-action", false );


  $rid = awm_pgexec( $wrms_db, "COMMIT", "wu-action", true );

  $because .= "<H2>Writeup Details Changed</H2>";
  $seq = intval($new['seq']);
?>