<?php
$debuglevel = 7;
  $rid = awm_pgexec( $wrms_db, "BEGIN;", "help-action", false );

  if ( eregi("update", $submit) ) {
    $query = "";
    reset($new);
    foreach ($new as $key => $value) {
      $query .= ", $key = '" . tidy($value) . "' ";
    }
    $query = substr( $query, 2);
    $query = "UPDATE help SET $query WHERE topic = '" . tidy($topic) . "'; ";
  }
  else {
    $fields = "";
    $values = "";
    $new['topic'] = $topic;
    reset($new);
    foreach ($new as $key => $value) {
      $fields .= ", $key";
      $values .= ", '" . tidy($value) . "' ";
    }
    $fields = substr( $fields, 2);
    $values = substr( $values, 2);
    $query = "INSERT INTO help ($fields) VALUES($values); ";
  }
  $rid = awm_pgexec( $wrms_db, $query, "help-action", false );


  $rid = awm_pgexec( $wrms_db, "COMMIT", "help-action", true );

  $because .= "<H2>Help Details Changes</H2>";
?>
