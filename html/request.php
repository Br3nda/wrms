<?php
  $new_uri = str_replace('request.php','wr.php',$REQUEST_URI);

  header("Location: $new_uri");
?>