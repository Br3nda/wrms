<?php
require_once("always.php");
require_once("authorisation-page.php");

$session->LoginRequired();

require_once("maintenance-page.php");
require_once("Request.class");

$wr = new Request($request_id);
if ( $wr->new_record ) {
  $edit = 1;
  unset( $request_id );
}
else {
  $title = ( intval($request_id) > 0 && $wr->request_id == 0
                 ? "Request $request_id Unavailable"
                 : "WR#$wr->request_id - $wr->brief" );
}
$show = 0;
$new = isset($edit) && intval($edit) && !isset($id);

if ( !$session->just_logged_in && $wr->AllowedTo(($wr->new_record?"create":"update")) && isset($_POST['submit']) ) {
  if ( $wr->Validate($wrf) ) {
    $wr->Write($wrf);
    $wr = new Request($request_id);
  }
}
elseif ( !$session->just_logged_in && isset($action) ) {
  $wr->Actions($wrf);
}

  require_once("top-menu-bar.php");
  require_once("page-header.php");
  echo '<script language="JavaScript" src="/js/request.js"></script>' . "\n";

  echo $wr->Render();

  include("page-footer.php");
?>
