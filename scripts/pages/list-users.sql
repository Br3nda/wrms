
INSERT INTO awm_page ( page_name, page_desc, page_type )
 VALUES( 'list-users', 'List Users', 'LIST' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Title', 1, 'TITLE', 'Users' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Access0', 2, 'ACCESS', '-99999|499|1000' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Access1', 999, 'ACCESS', '500|999999|99999' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Access2', 1000, 'HTML', '<P>Unauthorised</P>' );


INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Select', 5, 'SELECT',
 'SELECT *, awm_get_perorg_data( awm_perorg.perorg_id, ''email'' ) AS email
         FROM awm_usr, awm_perorg
         WHERE awm_usr.perorg_id = awm_perorg.perorg_id 
         ORDER BY awm_perorg.perorg_sort_key'
 );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Col-1', 110, 'COLUMN', 'username|Maint. ID' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Link-1', 111, 'LINK', '.*|view.php3?page=view-user&;save_actions=UPDATE&;username=%current_row->username%' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Col-2', 120, 'COLUMN', 'perorg_name|Full Name' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Col-3', 130, 'COLUMN', 'email|EMail' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Link-2', 131, 'LINK', '.*|mailto:%current_row->email%' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'Col-4', 140, 'COLUMN', 'last_accessed|Last Use' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'option-1', 200, 'OPTION', 'Add|view.php3?page=view-user&;save_actions=INSERT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-users', 'menu-1', 900, 'MENU', 'display' );



