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
if ( $wr->request_id == 0 ) {
  unset( $request_id );
  $edit = 1;
  $title = ( intval($request_id) > 0 ? "Request Unavailable" : "New Request" );
}
else {
  $title = "WR#$wr->request_id - $wr->brief";
}
$show = 0;
$new = isset($edit) && intval($edit) && !isset($id);

if ( $M != "LC" && $wr->AllowedTo("update") && isset($_POST['submit']) ) {
  if ( $wr->Validate($wrf) ) {
    $wr->Write($wrf);
    $wr = new Request($request_id);
  }
}
elseif ( $M != "LC" && isset($action) ) {
  $wr->Actions($wrf);
}

  include("headers.php");
  echo '<script language="JavaScript" src="/js/request.js"></script>' . "\n";

  echo $wr->Render();

  include("footers.php");
?>