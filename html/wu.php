<?php
  include("inc/always.php");
  include("inc/options.php");

  $can_edit = ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] );
  $can_vote = ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] );
  $can_cool = ( $roles['wrms']['Admin'] || $roles['wrms']['Support'] );
  $can_can = ( $roles['wrms']['Admin'] );

  $form = "wu";
  $nodename = str_replace("\\", "", $wu );
  $nodename = str_replace("/", "", $nodename );
  $nodename = str_replace("'", "''", $nodename );

  $last = intval("$last");

  if ( isset($node_id) ) {
    $node_id = intval($node_id);
    $current_node = $node_id;
  }

  error_log( "1", 0);
  if ( "$submit" <> "") {
    include("inc/$form-valid.php");
    if ( "$because" == "" ) include("inc/$form-action.php");
  }

  $title = "$system_name - " . ucfirst($form);
  $right_panel = true;
  error_log( "2", 0);
  include("inc/headers.php");

  error_log( "3", 0);
  include("inc/$form-form.php");
  error_log( "4", 0);

  include("inc/footers.php");

?>