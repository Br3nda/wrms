<?php
require_once("always.php");
require_once("authorisation-page.php");

if ( !$session->logged_in ) {
  // Very quiet
  echo "Error: Not authorised";
  exit;
}

if ( isset($org_code) ) {
  // Force the org_code to be appropriate for this user, if necessary
  if ( ! ($session->AllowedTo('Admin') || $session->AllowedTo('Support') ) ) {
    $org_code = $session->org_code;
  }
  // We sanitise this once, here, and then use it repeatedly...
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

  // TODO: Here we should consider restricting the list of systems
  // for some types of users.  E.g. external contractors, members
  // of large organisations that do not want all people to have
  // complete access to all W/Rs.
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

  $sql = "SELECT tag_id, tag_description ";
  $sql .= "FROM organisation_tag ";
  $sql .= "WHERE active AND organisation_tag.org_code = $org_code ";
  $sql .= "ORDER BY tag_sequence, lower(tag_description);";
  $qry = new PgQuery($sql);
  if ( $qry->Exec('js') ) {
    while( $row = $qry->Fetch() ) {
      echo "OrgTag: <option value=\"$row->tag_id\">$row->tag_description</option>\n";
    }
  }

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
else if ( isset($person_id) ) {
  $person_id = intval($person_id);
}
else if ( isset( $system_code ) ) {
}
else {
  echo "Error: Unrecognised request";
}
?>