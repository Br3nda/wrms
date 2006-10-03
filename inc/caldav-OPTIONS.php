<?php
  dbg_error_log("OPTIONS", "method handler");
  header( "Content-type: text/plain");
  header( "Allow: OPTIONS, GET, HEAD, POST, PUT, DELETE, REPORT");
  header( "DAV: 1, calendar-access");
?>