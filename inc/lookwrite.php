<?php

  switch ( $action ) {
    case "edit":
    case "delete":
      // Read the record first and set the screen values so the user can edit it and re-add it.
      $sql = "SELECT * FROM lookup_code WHERE source_table=? AND source_field=? AND lookup_code=?;";
      $q = new PgQuery($sql, $table, $field, $lookup_code);
      if ( $q->Exec("lookwrite") && $q->rows > 0 && $old = $q->Fetch() ) {
        $lookup_seq  = $old->lookup_seq;
        $lookup_desc = $old->lookup_desc;
        $lookup_misc = $old->lookup_misc;
      }

      if ( "$action" == "delete" ) {
        $sql = "DELETE FROM lookup_code WHERE source_table=? AND source_field=? AND lookup_code=?;";
        $q = new PgQuery($sql, $table, $field, $lookup_code);
        if ( $q->Exec("lookwrite") )
          $client_messages[] = "Lookup Code deleted.";
      }
      break;

    case "insert":
      $sql = "INSERT INTO lookup_code (source_table, source_field, lookup_code, ";
      $sql .= " lookup_seq, lookup_desc, lookup_misc) VALUES(?, ?, ?, ?, ?, ?);";
      $q = new PgQuery($sql, $table, $field, $lookup_code, $lookup_seq, $lookup_desc, $lookup_misc);
      if ( $q->Exec("lookwrite") )
        $client_messages[] = "Lookup Code added.";
      break;

    case "update":
      $sql = "UPDATE lookup_code SET lookup_code=?, lookup_seq=?, lookup_desc=?, lookup_misc=? ";
      $sql .= "WHERE source_table=? AND source_field=? AND lookup_code=?;";
      $q = new PgQuery($sql, $lookup_code, $lookup_seq, $lookup_desc, $lookup_misc, $table, $field, $old_lookup_code );
      if ( $q->Exec("lookwrite") )
        $client_messages[] = "Lookup Code updated.";
      break;
  }
?>