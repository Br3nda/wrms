<?php
  echo "<DIV BGCOLOR=#e0e0e0><H4>";
  if ( "$warn_msg" <> "" && "$error_qry$error_msg" == "" )
    echo "Note";
  else
    echo "Error Processing Request";
  echo ":</H4>";
  if ( "$error_loc" != "") echo "<P>Error occurred in: $error_loc</P>";

  if ( "$error_msg" != "") echo "<BR>$error_msg";
  if ( "$warn_msg" != "") echo "<BR>$warn_msg<BR>&nbsp;<BR>";
  echo "</P></DIV>";
?>
