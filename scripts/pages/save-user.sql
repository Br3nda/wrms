INSERT INTO awm_page ( page_name, page_desc, page_type ) VALUES( 'save-user', 'Save User Record', 'SAVE' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'save-user', 'Title', 1, 'TITLE', 'Update: %in_fullname% (%in_username%) ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'save-user', 'Access0', 2, 'ACCESS', '-99999|499|1000' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'save-user', 'Access1', 999, 'ACCESS', '500|999999|99999' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'save-user', 'Access2', 1000, 'HTML', '<P>Unauthorised</P>' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Insert-0', 50, 'INSERT', 
   'INSERT INTO awm_perorg ( perorg_name, perorg_sort_key, perorg_type )
     VALUES( ''%in_fullname%'',  name_to_sort_key( ''%in_fullname%''), ''P'' ) ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'php-1', 51, 'PHP3', 
   'if ( !strcmp( "INSERT", $save_actions) )
      $xxqry = "SELECT awm_perorg_id_from_name(''$in_fullname'');";
    else
      $xxqry = "SELECT perorg_id FROM awm_usr WHERE username = ''$username'';";
    $xrid = pg_Exec( $awmdb, $xxqry);
    $perorg_id = pg_Result( $xrid, 0, 0);
    $in_perorg_id = $perorg_id;
    if ( $in_access_level > $usr->access_level ) $in_access_level = $usr->access_level;
    if ( !isset($in_access_level) || $usr->access_level < 500 ) $in_access_level = "access_level";
    if ( !isset($in_validated) || $usr->access_level < 500 ) $in_validated = "validated";
    if ( !isset($in_enabled) || $usr->access_level < 500 ) $in_enabled = "enabled";
   ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Insert-2', 52, 'INSERT', 
   'INSERT INTO awm_usr ( username, perorg_id ) VALUES( ''%in_username%'', %perorg_id% ) ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Insert-3', 53, 'INSERT', 
   'INSERT INTO awm_perorg_data ( perorg_id, po_data_name, po_data_value ) VALUES( %perorg_id%, ''email'', ''%in_email%'' ) ' );


INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Update-0', 60, 'UPDATE', 
  'UPDATE awm_perorg SET perorg_name = ''%in_fullname%'' WHERE perorg_id = %perorg_id% ;' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Update-1', 61, 'UPDATE|INSERT', 
  'UPDATE awm_usr SET username = ''%in_username%'',
     validated = %in_validated%,
     enabled = %in_enabled%,
     access_level = %in_access_level%,
     password = ''%in_password%''
     WHERE username = ''%username%'';' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Update-3', 63, 'UPDATE', 
   'UPDATE awm_perorg_data SET po_data_value = ''%in_email%'' WHERE perorg_id = %perorg_id% AND po_data_name = ''email'' ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Update-4', 64, 'UPDATE|INSERT', 
   'DELETE FROM awm_perorg_rel WHERE perorg_rel_id = %perorg_id% AND perorg_rel_type = ''Employer'' ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Update-5', 65, 'UPDATE|INSERT', 
   'INSERT INTO awm_perorg_rel (perorg_id, perorg_rel_id, perorg_rel_type) VALUES( ''%in_org_code%'', %perorg_id%, ''Employer'') ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Table-1', 71, 'TABLE', 'usr' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Select-0', 100, 'SAVESELECT', 'sel_sysmgr|UPDATE work_system SET notify_usr = ''unknown'' WHERE notify_usr = ''%username%''||UPDATE work_system SET notify_usr = ''%in_username%'' WHERE system_code = ''%select-value%'' ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Select-1', 101, 'SAVESELECT', 'sel_sysmgr|DELETE FROM perorg_system WHERE perorg_id = %perorg_id% AND persys_role = ''SYSMGR''||INSERT INTO perorg_system (perorg_id, persys_role, system_code) VALUES( %perorg_id%, ''SYSMGR'', ''%select-value%'') ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Select-2', 102, 'SAVESELECT', 'sel_cltmgr|DELETE FROM perorg_system WHERE perorg_id = %perorg_id% AND persys_role = ''CLTMGR''||INSERT INTO perorg_system (perorg_id, persys_role, system_code) VALUES( %perorg_id%, ''CLTMGR'', ''%select-value%'') ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
  VALUES( 'save-user', 'Select-3', 103, 'SAVESELECT', 'sel_userof|DELETE FROM perorg_system WHERE perorg_id = %perorg_id% AND persys_role = ''USER''||INSERT INTO perorg_system (perorg_id, persys_role, system_code) VALUES( %perorg_id%, ''USER'', ''%select-value%'') ' );

