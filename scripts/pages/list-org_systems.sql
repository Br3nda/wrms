
INSERT INTO awm_page ( page_name, page_desc, page_type )
 VALUES( 'list-org_systems', 'List Organisation Systems', 'LIST' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-org_systems', 'Title', 1, 'TITLE', 'Organisation Systems' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-org_systems', 'Select', 5, 'SELECT',
 'SELECT *
         FROM perorg_system, work_system
         WHERE perorg_system.system_code = work_system.system_code
           AND perorg_system.perorg_id = %perorg_id% 
         ORDER BY perorg_system.system_code'
 );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-org_systems', 'Col-1', 110, 'COLUMN', 'perorg_id|ID #' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-org_systems', 'Col-2', 120, 'COLUMN', 'system_code|System' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-org_systems', 'Col-3', 130, 'COLUMN', 'system_desc|Description' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-org_systems', 'Link-1', 131, 'LINK', '.*|view.php3?page=view-system&;save_actions=UPDATE&;system_code=%current_row->system_code%' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-org_systems', 'Col-4', 140, 'COLUMN', 'persys_role|Role' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-org_systems', 'option-1', 200, 'OPTION', 'Add|view.php3?page=view-organisation&;save_actions=INSERT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-org_systems', 'menu-1', 900, 'MENU', 'display' );



