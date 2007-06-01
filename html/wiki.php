<?php
  include("always.php");
  require_once("authorisation-page.php");
  $session->LoginRequired();

  require_once("maintenance-page.php");
  require_once("Writeup.class");

  $node = new Writeup($wu);
  if ( $node->node_id == 0 ) {
    $title = ( $wu != "" ? "Page Unavailable" : "New Page" );
  }
  else {
    $title = "$node->nodename";
    if ( $node->firstline != "" && $node->firstline != $node->nodename )
      $title .= " - " . $node->firstline ;
  }
  $show = 0;

  if ( isset($_POST['submit']) ) {
    if ( ($user->user_no > 0 && $user->user_no == $session->user_no) ) {
      if ( $user->Validate($userf) ) {
        $user->Write($userf);
        $user = new User($user->user_no);
        if ( $user->user_no == 0 ) {
          $title = ( $user_no != "" ? "User Unavailable" : "New User" );
        }
        else {
          $title = "$user->user_no - $user->fullname";
        }
      }
    }
  }

  require_once("top-menu-bar.php");
  require_once("page-header.php");
  echo '<script language="JavaScript" src="/js/user.js"></script>' . "\n";
  echo $user->Render();
  include("page-footer.php");
  if (
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
  include("page-header.php");

  include("$form-form.php");

  include("page-footer.php");

?>
