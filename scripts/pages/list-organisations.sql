
INSERT INTO awm_page ( page_name, page_desc, page_type )
 VALUES( 'list-organisations', 'List Organisations', 'LIST' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Title', 1, 'TITLE', 'Organisations' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Select', 5, 'SELECT',
 'SELECT *, ''Systems''::text AS org_systems, ''People''::text AS employees
         FROM awm_perorg
         WHERE awm_perorg.perorg_type = ''O'' 
         ORDER BY awm_perorg.perorg_sort_key'
 );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Col-1', 110, 'COLUMN', 'perorg_id|ID #' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Col-2', 120, 'COLUMN', 'perorg_name|Organisation Name' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Link-1', 121, 'LINK', '.*|view.php3?page=view-organisation&;save_actions=UPDATE&;perorg_id=%current_row->perorg_id%' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Col-3', 130, 'COLUMN', 'perorg_sort_key|Sort Key' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Col-4', 140, 'COLUMN', 'org_systems| ' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Link-2', 141, 'LINK', '.*|view.php3?page=list-org_systems&;perorg_id=%current_row->perorg_id%' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Col-5', 150, 'COLUMN', 'employees| ' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'Link-3', 151, 'LINK', '.*|view.php3?page=list-perorg-child&;perorg_id=%current_row->perorg_id%&;perorg_rel_type=Employer' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'option-1', 200, 'OPTION', 'Add|view.php3?page=view-organisation&;save_actions=INSERT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-organisations', 'menu-1', 900, 'MENU', 'display' );



