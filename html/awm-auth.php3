<?php
  include( "awm/funcs/authenticate.php3" );
  $dbid = $awmdb;
  $homedir = "/var/www/wrms";
  $funcdir = "$homedir/funcs";
  if ( substr( "$HTTP_HOST", 0, 4) == "wrms" )
    $wrms_home = "http://wrms.cat-it.co.nz";
  else /* if ( substr( "$HTTP_HOST", 0, 4) == "heid" || substr( "$HTTP_HOST", 0, 5) == "local" ) */
    $wrms_home = "http://$HTTP_HOST/~andrew/wrms";
/*  else
    $wrms_home = "http://$HTTP_HOST";
  echo "<P>$wrms_home<BR>$HTTP_HOST</P>"; */
  $update_dir = "system_updates";
  $awm_home = "$wrms_home/awm";
  $query = "UPDATE awm_usr SET last_accessed = 'now' WHERE LOWER(username)=LOWER('$PHP_AUTH_USER')";
  pg_exec( $dbid, $query );
?>
