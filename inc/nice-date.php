<?php
  function nice_date($str) {
    return substr($str, 11, 5) . ", " . substr($str, 4, 6) . " " . substr($str, 20, 4);
  }
?>
