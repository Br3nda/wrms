INSERT INTO awm_page ( page_name, page_desc, page_type )
 VALUES( 'save-organisation', 'Save Organisation Record', 'SAVE' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'save-organisation', 'Title', 1, 'TITLE', 'Update: %in_perorg_name% (%perorg_id%) ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-organisation', 'Insert-0', 50, 'TABLE', 'awm_perorg' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-organisation', 'php-1', 51, 'PHP3', 
   'if ( !strcmp( "INSERT", $save_actions) ) {
      $xxqry = "SELECT awm_perorg_id_from_name(''$in_perorg_name'');";
      $xrid = pg_Exec( $awmdb, $xxqry);
      $perorg_id = pg_Result( $xrid, 0, 0);
    }
   ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-organisation', 'Select-1', 101, 'SAVESELECT', 'sel_userof|DELETE FROM perorg_system WHERE perorg_id = %perorg_id% AND persys_role = ''USER''||INSERT INTO perorg_system (perorg_id, persys_role, system_code) VALUES( %perorg_id%, ''USER'', ''%select-value%'') ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-organisation', 'Select-2', 102, 'SAVESELECT', 'sel_support|DELETE FROM perorg_system WHERE perorg_id = %perorg_id% AND persys_role = ''SUPPORT''||INSERT INTO perorg_system (perorg_id, persys_role, system_code) VALUES( %perorg_id%, ''SUPPORT'', ''%select-value%'') ' );

