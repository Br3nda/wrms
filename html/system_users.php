<?php
  include("always.php");
  require_once("authorisation-page.php");
  $session->LoginRequired("Admin,Support,OrgMgr");
  require_once("maintenance-page.php");
  require_once("organisation-selectors-sql.php");

function write_system_roles( $roles, $system_code ) {
  global $client_messages, $session;

  $users = "";
  $role_update = "";
  foreach( $roles AS $user_no => $role_code ) {
    if ( $role_code != "" ) {
      $user_no = intval($user_no);
      $users .= ( "$users" == "" ? "" : "," ) . $user_no;
      $role_update .= "SELECT set_system_role($user_no,'$system_code',". qpg($role_code).");";
    }
  }
  if ( $users == "" )
    $sql = "DELETE FROM system_usr WHERE system_code = '$system_code';";
  else
    $sql = "BEGIN; DELETE FROM system_usr WHERE system_code = '$system_code' AND user_no NOT IN ( $users ); $role_update COMMIT;";

    $q = new PgQuery($sql);
    if ( $q->Exec("SystemUsers::Write") )
      $client_messages[] = "System Roles updated.";
    else
      $client_messages[] = "There was a system problem writing to the database and no changes were made.";

}

  $system_code = str_replace( "'", "", str_replace("\\", "", $system_code ) );
  $title = "$system_name System Users";

  if ( "$system_code" == "" ) {
    $client_messages[] = "System Code was not supplied.";
    require_once("top-menu-bar.php");
    include("headers.php");
    include("footers.php");
    exit;
  }

  if ( $M != "LC" && isset($_POST['submit']) && is_array($_POST['role']))
    write_system_roles( $_POST['role'], $system_code );

  require_once("top-menu-bar.php");
  include("headers.php");

  // Select the possible system roles for the select boxes.
  $sql = "SELECT lookup_code, lookup_desc FROM lookup_code ";
  $sql .= "WHERE source_table = 'system_usr' AND source_field = 'role' ORDER BY lookup_seq;";
  $q = new PgQuery($sql);
  if ( $q && $q->Exec("SystemUsers::Roles") && $q->rows ) {
    $roles = array('_' => '-- No Access --');
    while( $row = $q->Fetch() ) {
      if ( !($session->AllowedTo('Admin') || $session->AllowedTo('Support'))
              && ($row->lookup_code == 'A' || $row->lookup_code == 'S') ) continue;
      $roles["_$row->lookup_code"] = $row->lookup_desc;
    }
  }

  // Select the users that we may want to include here.
  $sql = "SELECT usr.user_no, fullname, usr.org_code, org_name, system_usr.role, ";
  $sql .= "lookup_code AS role_code, lookup_desc AS role_desc, ";
  $sql .= "EXISTS (SELECT 1 FROM group_member WHERE usr.user_no = group_member.user_no ";
  $sql .=                 "AND group_member.group_no IN (SELECT group_no FROM ugroup WHERE group_name IN ('Admin','Support'))) ";
  $sql .= "AS internal_group, ";
  $sql .= "EXISTS (SELECT 1 FROM group_member WHERE usr.user_no = group_member.user_no ";
  $sql .=                 "AND group_member.group_no IN (SELECT group_no FROM ugroup WHERE group_name IN ('Contractor'))) ";
  $sql .= "AS contractor_group ";
  $sql .= "FROM usr NATURAL JOIN organisation ";
  $sql .= "LEFT JOIN system_usr ON (usr.user_no = system_usr.user_no AND system_usr.system_code = ?) ";
  $sql .= "LEFT JOIN lookup_code roles ON (source_table='system_usr' AND source_field='role' AND lookup_code=system_usr.role) ";
  $sql .= "WHERE usr.status != 'I' ";
  if ( isset( $org_code ) || $org_code == "" )
    $sql .= "AND usr.org_code=organisation.org_code ";
  else
    $sql .= "AND organisation.active ";

  $sql .= "AND ( EXISTS(SELECT 1 FROM org_system WHERE organisation.org_code = org_system.org_code AND org_system.system_code = ".qpg($system_code) .") ";
  if ( ( $session->AllowedTo("Admin") || $session->AllowedTo("Support") ) ) {
    $sql .= "OR EXISTS(SELECT 1 FROM usr u JOIN group_member USING (user_no) JOIN ugroup USING (group_no) ";
    $sql .= "WHERE organisation.org_code = u.org_code AND ugroup.group_name = 'Support') ";
  }
  $sql .= ") ";

  if ( !( $session->AllowedTo("Admin") || $session->AllowedTo("Support") ) ) {
    $sql .= "AND usr.org_code='$session->org_code' ";
  }

  $sql .= " ORDER BY (1000 - lookup_seq), LOWER(fullname);";
  $q = new PgQuery($sql, $system_code );

//  echo "<p>$q->querystring</p>";

  $search_record = (object) array();
  $ef = new EntryForm( $REQUEST_URI, $search_record, true );
  $ef->NoHelp();  // Prefer this style, for the moment

  echo $ef->StartForm( array("autocomplete" => "off" ) );

  if ( $q->Exec("SystemUsers::Users") && $q->rows ) {
    // Build table of usrs found
    echo <<<TABLEHEADINGS
<table border="0" cellpadding="2" cellspacing="1" align="center" width="100%">
<tr>
<th class="cols">Full Name</th>
<th class="cols">Organisation</th>
<th class="cols">User Role</th>
</tr>
TABLEHEADINGS;

    $line_format = <<<LINEFORMAT
<tr class="row%1d" title="%s">
<td class="sml"><a href="user.php?user_no=%d">%s</a></td>
<td class="sml"><a href="org.php?org_code=%d">%s</a></td>
<td class="sml" bgcolor="%s" align="center">%s</td>
</td></tr>
LINEFORMAT;

$role_colours = array( 'A' => '#ff5010', 'S' => '#e03000', 'C' => '#60a000',
                       'E' => '#80b020', 'O' => '#d0e070', 'V' => '#f0ff80' );

    $options = array_merge($roles, array("title" => "Select the role this person has in relation to this system"));
    $fld_format = '<span style="background-color: %s;">&nbsp; &nbsp; %s &nbsp; &nbsp;</span>';
    $i=0;
    while ( $row = $q->Fetch() ) {
      $search_record->role[$row->user_no] = $row->role;
      $html = sprintf($fld_format, $role_colours["$row->role"],
                                     $ef->DataEntryField( "", "select", "role[$row->user_no]", $options ) );
      $colour = '#e8ffe0';
      $type   = "This is a client";
      if ( $row->internal_group == 't' )        { $colour = '#ffe8e0'; $type = "This is an internal person"; }
      else if ( $row->contractor_group == 't' ) { $colour = '#e0e8ff'; $type = "This is an external support person"; }
      printf( $line_format, $i++%2, $type, $row->user_no, $row->fullname, $row->org_code, $row->org_name,
                            $colour, $html );
    }
    echo "</table>\n";

    echo '<div id="footer">';
    echo $ef->SubmitButton( "submit", "Update" );
    echo '</div>';
    echo $ef->EndForm();
  }

  include("footers.php");
?>