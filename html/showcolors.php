<?php
  include("inc/always.php");
  include("inc/options.php");

  $title = "$system_name - Showing Colors";
  include("inc/starthead.php");
  include("inc/formstyle.php");
  echo "</head>\n";
  echo "<BODY bgcolor=\"$colors[0]\" topmargin=\"0\" marginwidth=\"0\" marginheight=\"0\" leftmargin=\"0\">\n";
  include("inc/menuhead.php");

  echo "<table width=40% cellspacing=4 cellpadding=6 border=1 align=center>\n";
  for ( $i=0; $i < 10; $i++ ) {
    echo "<tr bgcolor=$colors[$i]><td>&nbsp;Color $i&nbsp;</td></tr>\n";
  }
  echo "</table>\n";
?>

</body> 
</html>


