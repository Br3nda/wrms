<?php
require_once("always.php");
require_once("authorisation-page.php");

// Header("Content-type: text/xml");
/* echo "<?xml version=\"1.0\"?>\n"; */
// echo "<selects>\n";
if ( !$session->logged_in ) {
  // Very quiet
  // echo "<error>Not authorised</error>";
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
    // echo "<personselect>\n";
    while( $row = $qry->Fetch() ) {
      echo "Person: <option value=\"$row->user_no\">$row->name</option>\n";
      // echo "<option>$row->user_no|$row->fullname</option>\n";
    }
    // echo "</personselect>\n";
  }

  $sql = "SELECT work_system.system_code, system_desc ";
  $sql .= "FROM work_system JOIN org_system ON (org_system.system_code = work_system.system_code) ";
  $sql .= "WHERE active AND org_system.org_code = $org_code ";
  $sql .= "ORDER BY lower(system_desc);";
  $qry = new PgQuery($sql);
  if ( $qry->Exec('js') ) {
    // echo "<systemselect>\n";
    while( $row = $qry->Fetch() ) {
      echo "System: <option value=\"$row->system_code\">$row->system_desc</option>\n";
    }
    // echo "</systemselect>\n";
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
// echo "</selects>\n";
?>