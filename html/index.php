<?php
  include("inc/always.php");
  include("inc/options.php");
  include("inc/starthead.php");
  include("inc/styledef.php");
  include("inc/bodydef.php");
  include("inc/menuhead.php");

//  echo "Session: $session_id -- $session->session_start<br>$query";

  echo "<br><table border=0 width=95% cellspacing=0 cellpadding=7><tr>\n";
  echo "<td width=3% nowrap> &nbsp; &nbsp; </td>\n";

  echo "<td VALIGN=TOP WIDTH=";
  if ( $logged_on ) echo "94"; else echo "60";
  echo "%>\n";

  if ( "$error_loc$error_msg$warn_msg" <> "" ) {
    include( "inc/error.php" );
  }
  if ( "$error_loc$error_msg" == "" ) {
    include("inc/indexpage.php");
  }

  echo "\n</td>\n";

  // The side bar - if they aren't logged on...
  if ( ! $logged_on ) {
    echo "<td VALIGN=TOP WIDTH=34%>\n";
    include("inc/sidebar.php");
    echo "\n</td>\n";
  }
  echo "<td WIDTH=3% NOWRAP> &nbsp; &nbsp; </td>\n";
// phpinfo();
  echo "</tr></table></body></html>";
?>

