<?php
  include("always.php");
  include("options.php");
  include("notify-emails.php");

  if ( $logged_on ) {
    include("getrequest.php");
    if ( isset($submit) && "$submit" != "" ) {
      include("request-valid.php");
      if ( "$because" == "" )
        include("request-action.php");
    }

    $title = "$system_name - Maintain Request";
    $left_panel = ("$style" != "plain");
    include("headers.php");

    include("request-form.php");
  }
  else {
    include("headers.php");
    echo "<h3>Please log on for access to work requests</h3>\n";
  }

include("footers.php");
?>
