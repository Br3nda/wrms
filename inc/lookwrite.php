<?php
  $because = "";

  if ( ! $logged_on ) {
    $because .= "You must log on with a valid password and maintainer ID\n";
  }

  // Validate that they are only maintaining a request for a system_code they
  if ( ! $roles[wrms][Admin] ) {
    $because = "This function is only available to administrators";
  }

  if ( "$because" <> "" ) {
    $because = "<H2>Errors with request:</H2>\n" . nl2br( $because ) . "<HR>\n";
    $because .= "<P><B>Changes have not been processed - please correct and re-submit</B></P>\n";
  }
  else {
    if ( "$action" == "edit" || "$action" == "delete" ) {
      // Read the record first and set the screen values so the user can edit it and re-add it.
      $query = "SELECT * FROM lookup_code WHERE source_table='$table' AND source_field='$field'";
      $query .= " AND lookup_code='$lookup_code'";
      $result = pg_Exec( $wrms_db, $query );
      if ( ! $result ) {
        echo "<p>Error in query<BR>$query</p>";
        exit;
      }
//      $because=$query;

      $deleted = pg_Fetch_Object( $result, 0);
      $lookup_seq  = $deleted->lookup_seq;
      $lookup_desc = $deleted->lookup_desc;
      $lookup_misc = $deleted->lookup_misc;

      if ( "$action" == "delete" ) {
        $query = "DELETE FROM lookup_code WHERE source_table='$table' AND source_field='$field'";
        $query .= " AND lookup_code='$lookup_code'";
        $result = pg_Exec( $wrms_db, $query );
        if ( ! $result ) {
          echo "<p>Error in query<BR>$query</p>";
          exit;
        }
        $because .= "<h3>Lookup Code deleted</h3>";
      }
    }
    else if ( "$action" == "insert" ) {
      $query = "INSERT INTO lookup_code (source_table, source_field, lookup_code, ";
      $query .= " lookup_seq, lookup_desc, lookup_misc) ";
      $query .= " VALUES('$table', '$field', '$lookup_code', ";
      $query .= " '$lookup_seq', '$lookup_desc', '$lookup_misc') ";
      $result = pg_Exec( $wrms_db, $query );
      if ( ! $result ) {
        echo "<p>Error in query<BR>$query</p>";
        exit;
      }
      $because .= "<h3>Lookup Code added</h3>";
    }
    else if ( "$action" == "update" ) {
      $query = "UPDATE lookup_code SET lookup_code='$lookup_code', ";
      $query .= " lookup_seq ='$lookup_seq' , ";
      $query .= " lookup_desc='$lookup_desc', ";
      $query .= " lookup_misc='$lookup_misc' ";
      $query .= " WHERE source_table='$table' AND source_field='$field'";
      $query .= " AND lookup_code='$old_lookup_code'";
      $result = pg_Exec( $wrms_db, $query );
      if ( ! $result ) {
        echo "<p>Error in query<BR>$query</p>";
        exit;
      }
      $because .= "<h3>Lookup Code updated</h3>";
    }
  }
?>

