<?php

  if ( "$because" <> "" ) {
    $because = "<H3>User Not Deleted</H3><P>$because</P>\n";
  }
  else {
    // Actually delete the usr...
    $query = "BEGIN TRANSACTION;";
    $result = pg_Exec( $wrms_db, $query );

    // OK, so if we have a valid user number...
    if ( isset($user_no) && $user_no > 0 ) {
      $query = "DELETE FROM usr WHERE user_no='$user_no'";
      $result = pg_Exec( $wrms_db, $query );
      if ( ! $result ) echo "<p>$query</p>";

      $query = "DELETE FROM group_member WHERE user_no=$user_no";
      $result = pg_Exec( $wrms_db, $query );
      if ( ! $result ) echo "<p>$query</p>";

      // Finally commit the transaction...
      $query = "COMMIT TRANSACTION;";
      $result = pg_Exec( $wrms_db, $query );
      $because = "<H3>User Record Deleted</H3>\n";


      // Delete system_code associations
      $query = "DELETE FROM system_usr WHERE user_no=$user_no";
      $result = pg_Exec( $wrms_db, $query );
      if ( ! $result ) echo "<p>$query</p>";
    }
  }

?>
