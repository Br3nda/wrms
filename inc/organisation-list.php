<?php
function get_organisation_list( $current="", $maxwidth=50 ) {
  global $session;

  $sql = "SELECT organisation.org_code, organisation.abbreviation || ' - ' || organisation.org_name FROM organisation ";
  if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) ) {
    $sql .= "JOIN org_system USING (org_code) ";
    $sql .= "JOIN work_system USING (system_code) ";
    $sql .= "JOIN system_usr ON (work_system.system_code = system_usr.system_code ";
    $sql .= "AND system_usr.user_no = $session->user_no ";
    $sql .= "AND system_usr.role = 'S') ";
  }
  $sql .= "WHERE organisation.active ORDER BY LOWER(org_name)";

  $q = new PgQuery($sql);
  $rid = awm_pgexec( $dbconn, $sql, "organisation-list");

  $org_code_list = $q->BuildOptionList($current,'GetOrgList');

  return $org_code_list;
}
