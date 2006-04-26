<?php
require_once("always.php");
require_once("authorisation-page.php");
require_once("organisation-selectors-sql.php");

header( 'Expires: ' . gmdate( 'D, d M Y H:i:s T') );
header( 'Cache-control: max-age=1, private' );
header( 'Pragma: no-cache' );

if ( !$session->logged_in ) {
  // Very quiet
  echo "Error: Not authorised";
  exit;
}

if ( isset($org_code) ) {

  // Force the org_code to be appropriate for this user, if necessary
  if ( ! ($session->AllowedTo('Admin') || $session->AllowedTo('Support') || $session->AllowedTo('Contractor') ) ) {
    $org_code = $session->org_code;
  }
  // We sanitise this once, here, and then use it repeatedly...
  $org_code = intval($org_code);

  $qry = new PgQuery( SqlSelectRequesters($org_code) );
  if ( $qry->Exec('js::Person') ) {
    while( $row = $qry->Fetch() ) {
      echo "Person: <option value=\"$row->user_no\">$row->name</option>\n";
    }
  }

  $qry = new PgQuery( SqlSelectSubscribers($org_code) );
  if ( $qry->Exec('js::Subscriber') ) {
    while( $row = $qry->Fetch() ) {
      echo "Subscriber: <option value=\"$row->user_no\">$row->name</option>\n";
    }
  }


  $qry = new PgQuery( SqlSelectSystems($org_code) );
  if ( $qry->Exec('js::System') ) {
    while( $row = $qry->Fetch() ) {
      echo "System: <option value=\"$row->system_id\">$row->system_desc</option>\n";
    }
  }


  $qry = new PgQuery( SqlSelectOrgTags($org_code) );
  if ( $qry->Exec('js::OrgTag') ) {
    while( $row = $qry->Fetch() ) {
      echo "OrgTag: <option value=\"$row->tag_id\">$row->tag_description</option>\n";
    }
  }

}
else if ( isset($person_id) ) {
  $person_id = intval($person_id);
}
else if ( isset( $system_id ) ) {
}
else {
  echo "Error: Unrecognised request";
}
?>
