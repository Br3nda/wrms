<?php
  include("inc/always.php");
  include("inc/options.php");

  if ( "$submit" <> "") {
    include("inc/$form-valid.php");
    if ( "$because" == "" ) include("inc/$form-action.php");
//    $because = "<h2>" . ucfirst($form) . " Form Submitted</h2>$because";
  }

  $title = "$system_name - " . ucfirst($form);
  include("inc/starthead.php");
  include("inc/formstyle.php");
  echo "</head>\n";
  echo "<BODY bgcolor=\"$colors[0]\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" leftmargin=\"0\">\n";
  include("inc/menuhead.php");

  if ( "$submit" <> "" ) {
    echo "$because";
  }

  include("inc/getrequest.php");
  include("inc/$form-form.php");
?>

</body> 
</html>


