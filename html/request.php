<?php
  include("inc/always.php");
  include("$base_dir/inc/options.php");
  include("$base_dir/inc/notify-emails.php");
  include("$base_dir/inc/nice-date.php");

  if ( $logged_on ) {
    include("$base_dir/inc/getrequest.php");
    if ( "$submit" != "" ) {
      include("$base_dir/inc/request-valid.php");
      if ( "$because" == "" )
        include("$base_dir/inc/request-action.php");
    }

    $title = "$system_name - Maintain Request";
    include("$base_dir/inc/starthead.php");
    include("$base_dir/inc/styledef.php");
    echo "</head>\n<body BGCOLOR=$colors[0] LEFTMARGIN=0 TOPMARGIN=0 MARGINHEIGHT=0 MARGINWIDTH=0>\n";

    include("$base_dir/inc/menuhead.php");
    include("$base_dir/inc/request-head.php");

    include("$base_dir/inc/request-form.php");
  }
  else {
    include("$base_dir/inc/starthead.php");
    include("$base_dir/inc/styledef.php");
    include("$base_dir/inc/bodydef.php");
    include("$base_dir/inc/menuhead.php");
    echo "<br><table BORDER=0 WIDTH=95% CELLSPACING=0 CELLPADDING=7><tr>\n";
    echo "<td WIDTH=3% NOWRAP> &nbsp; &nbsp; </td>\n";

    echo "<td VALIGN=TOP WIDTH=";
    if ( $logged_on ) echo "94"; else echo "60";
    echo "%>\n";

    if ( "$error_loc$error_msg$warn_msg" <> "" ) {
      include( "inc/error.php" );
    }
    if ( "$error_loc$error_msg" == "" ) {
      include("$base_dir/inc/indexpage.php");
    }

    echo "\n</td>\n";

    // The side bar - if they aren't logged on...
    if ( ! $logged_on ) {
      echo "<td VALIGN=TOP WIDTH=34%>\n";
      include("$base_dir/inc/sidebar.php");
      echo "\n</td>\n";
    }
    echo "<td WIDTH=3% NOWRAP> &nbsp; &nbsp; </td>\n";
    echo "</tr></table>";
  }
?>
</body> 
</html>


