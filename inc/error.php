<?php
  echo "<div class=error><h4>";
  if ( "$warn_msg" <> "" && "$error_qry$error_msg" == "" )
    echo "Note";
  else
    echo "Error Processing Request";
  echo ":</h4>";
  if ( "$error_loc" != "") echo "<p>Error occurred in: $error_loc</p>";

  if ( "$error_msg" != "") echo "<br />$error_msg";
  if ( "$warn_msg" != "") echo "<br />$warn_msg<br />&nbsp;<br />";
  echo "</p></div>";
?>