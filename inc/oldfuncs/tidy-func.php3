<?php
  function tidy($str) {
    $tidied = ereg_replace("\\\'", "'", $str);
    $tidied = ereg_replace("'", "''", $tidied);
    return $tidied;
  }
?>
