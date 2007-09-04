<?php
/**
* These functions create the SQL for some complex queries related to organisations
*
* @package   WRMS
* @subpackage   WRMSSession
* @author    Andrew McMillan <andrew@catalyst.net.nz>
* @copyright Catalyst .Net Ltd
* @license   http://gnu.org/copyleft/gpl.html GNU GPL v2
*/

/**
* Select list of organisations appropriate for this user
* @param int org_code An optional organisation code to include, even if inactive
* @return An SQL statement
*/
function SqlSelectOrganisations( $org_code = 0 ) {
  global $session;

  $sql = "SELECT organisation.org_code, organisation.org_name || ' (' || organisation.abbreviation || ')' AS org_name ";
  $sql .= "FROM organisation ";
  $sql .= "WHERE (organisation.active ";
  if ( $org_code > 0 && $session->AllowedTo("see_other_orgs") ) {
    $sql .= "OR organisation.org_code = $org_code ";
  }
  $sql .= ") AND abbreviation !~ '^ *$' ";
  if ( ! $session->AllowedTo("Contractor") && ! $session->AllowedTo("see_other_orgs") ) {
    $sql .= "AND organisation.org_code = $session->org_code ";
  }
  elseif ( $session->AllowedTo("Contractor") && ! $session->AllowedTo("see_other_orgs") ) {

    //  They could make requests for organisations that use systems they are Allocatable/Support for...
    $sql .= "AND ( EXISTS (SELECT 1 FROM org_system ";
    $sql .= "JOIN system_usr USING (system_id) ";
    $sql .= "WHERE system_usr.role IN ('A','S') ";
    $sql .= "AND system_usr.user_no = $session->user_no ";
    $sql .= "AND org_system.org_code = organisation.org_code) ";

    //  Or they could make requests for their own organisation too...
    $sql .= "OR organisation.org_code = $session->org_code )";
  }
  $sql .= "ORDER BY lower(org_name)";

  return $sql;
}


/**
* Organisation people are anyone with appropriate system access.
* Client people see people from their own organisation and the
*             people who support their system.
* Contractors see people from organisations for the systems that
*             they support.
* Support / Admin people see everyone from the two organisations.
*
* @param int org_code Only people from this organisation
* @param int allow_inactive_user Include this user, even if inactive
* @return An SQL statement
*/
function SqlSelectRequesters( $org_code = 0, $allow_inactive_user = 0 ) {
  global $session;

  $allow_inactive_user = intval($allow_inactive_user);
  $sql = "SELECT usr.user_no, fullname || ' (' || abbreviation || ')' AS name, lower(fullname) ";
  $sql .= "FROM usr ";
  $sql .= "JOIN organisation USING (org_code) ";
  $sql .= "WHERE organisation.active ";
  $sql .= "AND (usr.active OR usr.user_no=$allow_inactive_user) ";
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
  else if ( $org_code == 0 || ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") || $session->AllowedTo("Contractor") ) ) {
    $sql .= "AND organisation.org_code = $session->org_code ";
    $sql .= "AND ( EXISTS ";
    $sql .= "(SELECT 1 FROM usr u1 JOIN system_usr su1 USING (user_no) ";
    $sql .= "JOIN usr u2 ON (u1.org_code=u2.org_code AND u1.user_no != u2.user_no) ";
    $sql .= "JOIN system_usr su2 ON (su1.system_id=su2.system_id AND su2.user_no=u2.user_no) ";
    $sql .= "WHERE su2.role IN ('V','E','C') ";
    $sql .= "AND u1.user_no = $session->user_no ";
    $sql .= "AND u1.org_code = $session->org_code ";
    $sql .= "AND u2.user_no = usr.user_no) ";
    $sql .= " OR usr.user_no = $session->user_no ) ";
  }
  $sql .= " ORDER BY 3";

  return $sql;
}


/**
* Subscribed / Allocated list are organisation people (see
*       above) + anyone with a 'Support' system role on
*       systems this user has any access to.
*
* @param int org_code Only people from this organisation
* @param int allow_inactive_user Include this user, even if inactive
* @return An SQL statement
*/
function SqlSelectSubscribers( $org_code = 0, $allow_inactive_user = 0 ) {
  global $session;

  $allow_inactive_user = intval($allow_inactive_user);
  $sql = "SELECT usr.user_no, fullname || ' (' || abbreviation || ')' AS name, lower(fullname) ";
  $sql .= "FROM usr JOIN organisation USING(org_code) ";
  $sql .= "WHERE EXISTS(SELECT 1 FROM system_usr su ";
  $sql .=          "JOIN system_usr me USING(system_id) ";
  $sql .=          "WHERE me.user_no = $session->user_no AND su.user_no = usr.user_no AND su.role IN ('S','A') ) ";
  $sql .= "AND (usr.active OR usr.user_no=$allow_inactive_user) ";
  $sql .= "UNION ";
  $sql .= SqlSelectRequesters($org_code);  // Which will ORDER BY 3 across the whole result

  return $sql;
}


/**
* OrgTags are those for this organisation.  For a Support
*       /Admin it is those for any organisation (or the
*       specified organisation.  For a Contractor it is
*       for the organisations who have access to the
*       systems they provide support for.
*
* @param int org_code Only people from this organisation
* @return An SQL statement
*/
function SqlSelectOrgTags( $org_code = 0 ) {
  global $session;

  $sql = "SELECT tag_id, tag_description ";
  if ( $org_code == 0 && ($session->AllowedTo("Admin") || $session->AllowedTo("Support") || $session->AllowedTo("Contractor") ) )
    $sql .= " || ' (' || abbreviation || ')' AS tag_description ";

  $sql .= "FROM organisation JOIN organisation_tag USING(org_code) ";
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


/**
* The systems that a person has access to - all, or the
*     one specified if you're Support/Admin.  Anyone else
*     is restricted to the one's for their organisation.
*
* @param int org_code Only people from this organisation
* @return An SQL statement
*/
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