<?php
  include("always.php");
  require_once("authorisation-page.php");
  $session->LoginRequired("Admin,Support,OrgMgr");
  require_once("maintenance-page.php");
  require_once("organisation-selectors-sql.php");

function write_system_roles( $roles, $system_code ) {
  global $client_messages, $session;

  // Write the changes that have been submitted
  $sql = '';
  $delete_template = "DELETE FROM system_usr WHERE system_code = ".qpg($system_code)." AND user_no=%d;";
  $insert_template = "INSERT INTO system_usr system_code, user_no, role) VALUES( ".qpg($system_code).", %d, %s);";
  foreach( $roles AS $user_no => $role_code ) {
    $user_no = intval($user_no);
    $sql .= sprintf( $delete_template, $user_no );
    if ( $role_code != "" ) {
      $role_code = qpg($role_code);
      $sql .= sprintf( $insert_template, $user_no, $role_code );
    }
  }
  if ( $sql != "" ) {
    $q = new PgQuery( "BEGIN;$sql;COMMIT;" );
    if ( $q->Exec("SystemUsers::Write") )
      $client_messages[] = "System Roles updated.";
    else
      $client_messages[] = "There was a system problem writing to the database and no changes were made.";
  }
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

  if ( $M != "LC" && isset($_POST['submit']) && is_array($_POST['roles']))
    write_system_roles( $_POST['roles'], $system_code );
  else
    $session->Log("DBG: M=%s, submit=%s, array(roles)=%s", $M, $_POST['submit'], is_array($_POST['roles']) );

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
  $sql .= "lookup_code AS role_code, lookup_desc AS role_desc ";
  $sql .= "FROM usr NATURAL JOIN organisation ";
  $sql .= "LEFT JOIN system_usr ON (usr.user_no = system_usr.user_no AND system_usr.system_code = ?) ";
  $sql .= "LEFT JOIN lookup_code roles ON (source_table='system_usr' AND source_field='role' AND lookup_code=system_usr.role) ";
  $sql .= "WHERE TRUE ";
  if ( isset( $org_code ) || $org_code == "" )
    $sql .= "AND usr.org_code=organisation.org_code ";
  else
    $sql .= "AND organisation.active ";

  $sql .= "AND usr.status != 'I' ";

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
<tr class="row%1d">
<td class="sml"><a href="user.php?user_no=%d">%s</a></td>
<td class="sml"><a href="org.php?org_code=%d">%s</a></td>
<td class="sml">%s</td>
</td></tr>
LINEFORMAT;

$role_colours = array( 'A' => 'lightgreen', 'S' => 'orange', 'C' => 'lightblue', 'E' => 'pink', 'O' => 'cream' );

    $options = array_merge($roles, array("title" => "Select the role this person has in relation to this system"));
    $fld_format = '<span style="background-color: %s;">&nbsp; %s &nbsp;</span>';
    $i=0;
    while ( $row = $q->Fetch() ) {
      $search_record->role[$row->user_no] = $row->role;
      error_log( "DBG: $row->user_no=$row->role  => ".$search_record->role[$row->user_no]." => ".$search_record->record->role[$row->user_no]);
      $html = sprintf($fld_format, $role_colours["$row->role"],
                                     $ef->DataEntryField( "", "select", "role[$row->user_no]", $options ) );
      printf( $line_format, $i++%2, $row->user_no, $row->fullname, $row->org_code, $row->org_name, $html );
    }
    echo "</table>\n";

    echo '<div id="footer">';
    echo $ef->SubmitButton( "submit", "Update" );
    echo '</div>';
    echo $ef->EndForm();
  }

  include("footers.php");
?>