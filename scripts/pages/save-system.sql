INSERT INTO awm_page ( page_name, page_desc, page_type ) VALUES( 'save-system', 'Save System Record', 'SAVE' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'save-system', 'Title', 1, 'TITLE', 'Update System ''%in_system_code%'' ' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'save-system', 'Table', 50, 'TABLE', 'work_system' );
 

