<?php
  include("inc/always.php");
  include("$base_dir/inc/options.php");
  include("$base_dir/inc/notify-emails.php");
  include("$base_dir/inc/nice-date.php");

  include("$base_dir/inc/getrequest.php");
  if ( "$submit" != "" ) {
    include("$base_dir/inc/request-valid.php");
    if ( "$because" == "" ) {
      include("$base_dir/inc/request-action.php");
      include("$base_dir/inc/getrequest.php");
    }
  }

  $title = "$system_name - Maintain Request";
  include("$base_dir/inc/starthead.php");
  include("$base_dir/inc/formstyle.php");
  echo "</head>\n<body BGCOLOR=$colors[0] LEFTMARGIN=0 TOPMARGIN=0 MARGINHEIGHT=0 MARGINWIDTH=0>\n";

  include("$base_dir/inc/menuhead.php");

  include("$base_dir/inc/request-form.php");
?>

</body> 
</html>


