<?php
require_once("always.php");
require_once("authorisation-page.php");
if ( !$session->logged_in ) {
  include("headers.php");
  echo "<h3>Please log on for access to work requests</h3>\n";
  include("footers.php");
  exit;
}

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

error_log("$M != "LC" && Allowed=".$wr->AllowedTo("update")." && _POST[submit]=".isset($_POST['submit']);

if ( $M != "LC" && $wr->AllowedTo("update") && isset($_POST['submit']) ) {
  if ( $wr->Validate($wrf) ) {
    $wr->Write($wrf);
    $wr = new Request($request_id);
  }
}
elseif ( $M != "LC" && isset($action) ) {
  $wr->Actions($wrf);
}

  require_once("top-menu-bar.php");
  require_once("headers.php");
  echo '<script language="JavaScript" src="/js/request.js"></script>' . "\n";

  echo $wr->Render();

  include("footers.php");
?>
