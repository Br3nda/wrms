<?php

  // Force a new password display.

  include("always.php");
  require_once("Session.php");

  $session->SendTemporaryPassword();

?>