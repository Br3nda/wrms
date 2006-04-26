<?php
function get_organisation_list( $current="", $maxwidth=50 ) {
  global $session;

  $sql = "SELECT organisation.org_code, organisation.abbreviation || ' - ' || organisation.org_name FROM organisation ";
  if ( ! ($session->AllowedTo("Admin") || $session->AllowedTo("Support") ) ) {
    $sql .= "JOIN org_system USING (org_code) ";
    $sql .= "JOIN work_system USING (system_id) ";
    $sql .= "JOIN system_usr ON (work_system.system_id = system_usr.system_id ";
    $sql .= "AND system_usr.user_no = $session->user_no ";
    $sql .= "AND system_usr.role = 'S') ";
  }
  $sql .= "WHERE organisation.active ORDER BY LOWER(org_name)";

  $q = new PgQuery($sql);
  $org_code_list = $q->BuildOptionList($current,'GetOrgList');

  return $org_code_list;
}
