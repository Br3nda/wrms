<?php
require_once("always.php");
require_once("authorisation-page.php");

if ( !$session->logged_in ) {
  // Very quiet
  echo "Error: Not authorised";
  exit;
}

if ( isset($org_code) ) {
  $org_code = intval($org_code);
  $sql = "SELECT user_no, fullname || ' (' || abbreviation || ')' AS name ";
  $sql .= "FROM usr JOIN organisation ON (usr.org_code = organisation.org_code) ";
  $sql .= "WHERE status != 'I' AND usr.org_code = $org_code ";
  $sql .= "ORDER BY lower(fullname)";
  $qry = new PgQuery( $sql );
  if ( $qry->Exec('js') ) {
    while( $row = $qry->Fetch() ) {
      echo "Person: <option value=\"$row->user_no\">$row->name</option>\n";
    }
  }

  $sql = "SELECT work_system.system_code, system_desc ";
  $sql .= "FROM work_system JOIN org_system ON (org_system.system_code = work_system.system_code) ";
  $sql .= "WHERE active AND org_system.org_code = $org_code ";
  $sql .= "ORDER BY lower(system_desc);";
  $qry = new PgQuery($sql);
  if ( $qry->Exec('js') ) {
    while( $row = $qry->Fetch() ) {
      echo "System: <option value=\"$row->system_code\">$row->system_desc</option>\n";
    }
  }

  if ( isset($org2_code) || $session->org_code != $org_code ) {
    $org2_code = intval($org2_code);
    $sql = "SELECT user_no, abbreviation || ': ' || fullname AS name ";
    $sql .= "FROM usr JOIN organisation ON (usr.org_code = organisation.org_code) ";
    $sql .= "WHERE status != 'I' ";
    $sql .= "AND organisation.org_code IN ($org_code";
    if ( $session->org_code != $org_code ) {
      $sql .= ", $session->org_code";
    }
    if ( $org2_code > 0 && $session->org_code != $org2_code ) {
      $sql .= ", $org2_code";
    }
    $sql .= ") ORDER BY organisation.org_code, lower(fullname)";
    $qry = new PgQuery( $sql );
    if ( $qry->Exec('js') ) {
      while( $row = $qry->Fetch() ) {
        echo "Subscriber: <option value=\"$row->user_no\">$row->name</option>\n";
      }
    }
  }

}
else if ( isset($person_id) ) {
  $person_id = intval($person_id);
}
else if ( isset( $system_code ) ) {
}
else {
  echo "Error: Unrecognised request";
}
?>