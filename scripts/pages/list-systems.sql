INSERT INTO awm_page ( page_name, page_desc, page_type )
 VALUES( 'list-systems', 'List Systems', 'LIST' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-systems', 'Title', 1, 'TITLE', 'Systems' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-systems', 'Select', 5, 'SELECT', 'SELECT * FROM work_system ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-systems', 'Col-1', 110, 'COLUMN', 'system_code|Code' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-systems', 'Link-1', 111, 'LINK', '.*|view.php3?page=view-system&;save_actions=UPDATE&;system_code=%current_row->system_code%' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-systems', 'Col-2', 120, 'COLUMN', 'system_desc|System Name' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-systems', 'Col-3', 130, 'COLUMN', 'notify_usr|Notify User' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-systems', 'Link-3', 131, 'LINK', '.*|view.php3?page=view-user&;save_actions=UPDATE&;username=%current_row->notify_usr%' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-systems', 'option-1', 200, 'OPTION', 'Add|view.php3?page=view-system&;save_actions=INSERT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-systems', 'menu-1', 900, 'MENU', 'display' );

