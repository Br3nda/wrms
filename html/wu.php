<?php
  include("always.php");
  include("options.php");

  $can_edit = is_member_of('Admin','Support' );
  $can_vote = is_member_of('Admin','Support' );
  $can_cool = is_member_of('Admin','Support' );
  $can_can  = is_member_of('Admin' );

  $form = "wu";
  $nodename = str_replace("\\", "", $wu );
  $nodename = str_replace("/", "", $nodename );
  $nodename = str_replace("'", "''", $nodename );

  $last = intval("$last");

  if ( isset($node_id) ) {
    $node_id = intval($node_id);
    $current_node = $node_id;
  }

  if ( "$submit" <> "") {
    include("$form-valid.php");
    if ( "$because" == "" ) include("$form-action.php");
  }

  $title = "$system_name - " . ucfirst($form);
  $right_panel = true;
  include("headers.php");

  include("$form-form.php");

  include("footers.php");

?>
