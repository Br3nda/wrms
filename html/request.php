<?php
  include("inc/always.php");
  include("$base_dir/inc/options.php");
  include("$base_dir/inc/notify-emails.php");

  if ( $logged_on ) {
    include("$base_dir/inc/getrequest.php");
    if ( "$submit" != "" ) {
      include("$base_dir/inc/request-valid.php");
      if ( "$because" == "" )
        include("$base_dir/inc/request-action.php");
    }

    $title = "$system_name - Maintain Request";
    $left_panel = ("$style" != "plain");
    include("$base_dir/inc/headers.php");
    include("$base_dir/inc/request-head.php");

    include("$base_dir/inc/request-form.php");
  }
  else {
    include("$base_dir/inc/headers.php");
    echo "<h3>Please log on for access to work requests</h3>\n";
  }

include("$base_dir/inc/footers.php");
?>
