<?php
  $new_uri = str_replace('usr.php','user.php',$REQUEST_URI);

  header("Location: $new_uri");
?>