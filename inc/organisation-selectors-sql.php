<?php
//////////////////////////////////////////////////////////
// Select list of appropriate organisations
//////////////////////////////////////////////////////////
function SqlSelectOrganisations( $org_code = 0 ) {
  global $session;

  $sql = "SELECT organisation.org_code, organisation.org_name || ' (' || organisation.abbreviation || ')' AS org_name ";
  $sql .= "FROM organisation ";
  if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) ) {
    if ( !$session->AllowedTo("Contractor") ) {
      $sql .= "JOIN usr u ON (u.org_code = organisation.org_code AND u.user_no = $session->user_no) ";
    }
  }
  $sql .= "WHERE (organisation.active ";
  if ( "$org_code" != "" && ($session->AllowedTo("Admin") || $session->AllowedTo("Support")) ) {
    $sql .= "OR organisation.org_code = $org_code ";
  }
  $sql .= ") AND abbreviation !~ '^ *$' ";
  $sql .= "AND EXISTS(SELECT work_system.system_id FROM org_system JOIN work_system ON (org_system.system_id = work_system.system_id) WHERE org_system.org_code = organisation.org_code AND work_system.active) ";
  if ( $session->AllowedTo("Contractor") && ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) ) {
    //  They could make requests for organisations that use systems they are Allocatable/Support for...
    $sql .= "AND ( EXISTS (SELECT 1 FROM org_system ";
    $sql .= "JOIN system_usr USING (system_id) ";
    $sql .= "WHERE system_usr.role IN ('A','S') ";
    $sql .= "AND system_usr.user_no = $session->user_no ";
    $sql .= "AND org_system.org_code = organisation.org_code) ";
    //  Or they could make requests for their own organisation too...
    $sql .= "OR EXISTS (SELECT 1 FROM usr WHERE usr.user_no = $session->user_no ";
    $sql .= "AND usr.org_code = organisation.org_code ) )";
  }
  $sql .= "ORDER BY lower(org_name)";

  return $sql;
}


//////////////////////////////////////////////////////////
// Organisation people are anyone with appropriate system access.
// Client people see people from their own organisation and the
//             people who support their system.
// Contractors see people from organisations for the systems that
//             they support.
// Support / Admin people see everyone from the two organisations.
//////////////////////////////////////////////////////////
function SqlSelectRequesters( $org_code = 0 ) {
  global $session;

  $sql = "SELECT usr.user_no, fullname || ' (' || abbreviation || ')' AS name, lower(fullname) ";
  $sql .= "FROM usr ";
  $sql .= "JOIN organisation USING (org_code) ";
  $sql .= "WHERE status != 'I' ";
  if ( $org_code != 0 && ($session->AllowedTo("Admin") || $session->AllowedTo("Support")  || $session->AllowedTo("Contractor")) ) {
    $sql .= "AND organisation.org_code = $org_code ";
    if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support")) ) {
/*
      $sql .= "AND EXISTS (SELECT 1 FROM org_system ";
      $sql .= "JOIN system_usr USING (system_id) ";
      $sql .= "WHERE system_usr.role IN ('A','S') ";
      $sql .= "AND system_usr.user_no = $session->user_no ";
      $sql .= "AND org_system.org_code = organisation.org_code) ";
*/
    //  It could be for organisations that use systems they are Allocatable/Support for...
    $sql .= "AND ( EXISTS (SELECT 1 FROM org_system ";
    $sql .= "JOIN system_usr USING (system_id) ";
    $sql .= "WHERE system_usr.role IN ('A','S') ";
    $sql .= "AND system_usr.user_no = $session->user_no ";
    $sql .= "AND org_system.org_code = $org_code) ";
    //  Or they could make requests for their own organisation too...
    $sql .= "OR EXISTS (SELECT 1 FROM usr WHERE usr.user_no = $session->user_no ";
    $sql .= "AND usr.org_code = $org_code ) )";
    }
  }
  else if ( $org_code == 0 || ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") || $session->AllowedTo("Contractor") ) )
    $sql .= "AND organisation.org_code = $session->org_code ";
  $sql .= " ORDER BY 3";

  return $sql;
}


//////////////////////////////////////////////////////////
// Subscribed / Allocated list are organisation people (see
//       above) + anyone with a 'Support' system role on
//       systems this user has any access to.
//////////////////////////////////////////////////////////
function SqlSelectSubscribers( $org_code = 0 ) {
  global $session;

  $sql = "SELECT usr.user_no, fullname || ' (' || abbreviation || ')' AS name, lower(fullname) ";
  $sql .= "FROM usr JOIN organisation USING(org_code) ";
  $sql .= "WHERE EXISTS(SELECT 1 FROM system_usr su ";
  $sql .=          "JOIN system_usr me USING(system_id) ";
  $sql .=          "WHERE me.user_no = $session->user_no AND su.user_no = usr.user_no AND su.role IN ('S','A') ) ";
  $sql .= "AND usr.status != 'I' ";
  $sql .= "UNION ";
  $sql .= SqlSelectRequesters($org_code);

  return $sql;
}


//////////////////////////////////////////////////////////
// OrgTags are those for this organisation.  For a Support
//       /Admin it is those for any organisation (or the
//       specified organisation.  For a Contractor it is
//       for the organisations who have access to the
//       systems they provide support for.
//////////////////////////////////////////////////////////
function SqlSelectOrgTags( $org_code = 0 ) {
  global $session;

  $sql = "SELECT tag_id, tag_description ";
  if ( $org_code == 0 && ($session->AllowedTo("Admin") || $session->AllowedTo("Support") || $session->AllowedTo("Contractor") ) )
    $sql .= " || ' (' || abbreviation || ')' AS tag_description ";

  $sql .= "FROM organisation NATURAL JOIN organisation_tag ";
  $sql .= "WHERE organisation.active AND organisation_tag.active ";
  if ( $org_code != 0 && ($session->AllowedTo("Admin") || $session->AllowedTo("Support") || $session->AllowedTo("Contractor") ) )
    $sql .= "AND organisation.org_code = $org_code ";
  else if ( ($session->AllowedTo("Contractor") ) ) {
    $sql .= "AND EXISTS( SELECT 1 FROM system_usr me JOIN org_system USING(system_id) ";
    $sql .=         "WHERE me.user_no=$session->user_no AND me.role='S' ";
    $sql .=           "AND org_system.org_code = organisation.org_code) ";
  }
  else if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= "AND organisation.org_code = $session->org_code ";
  $sql .= "ORDER BY lower(abbreviation), tag_sequence, lower(tag_description)";

  return $sql;
}


//////////////////////////////////////////////////////////
// The systems that a person has access to - all, or the
//     one specified if you're Support/Admin.  Anyone else
//     is restricted to the one's for their organisation.
//////////////////////////////////////////////////////////
function SqlSelectSystems( $org_code = 0 ) {
  global $session;

  $sql = "SELECT work_system.system_id, system_desc ";
  $sql .= "FROM work_system ";
  if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) ) {
    $sql .= "JOIN system_usr ON (work_system.system_id=system_usr.system_id AND system_usr.user_no=$session->user_no) ";
  }
  $sql .= "WHERE active ";
  $sql .= "AND EXISTS (SELECT 1 FROM org_system WHERE org_system.system_id = work_system.system_id ";
  if ( $org_code != 0 && ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= "AND org_system.org_code = $org_code ";
  else if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) )
    $sql .= "AND org_system.org_code = $session->org_code ";
  $sql .= ") ";
  $sql .= "ORDER BY lower(system_desc);";

  return $sql;
}

?>