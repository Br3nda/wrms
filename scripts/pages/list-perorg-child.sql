
INSERT INTO awm_page ( page_name, page_desc, page_type )
 VALUES( 'list-perorg-child', 'List Related Persons/Organisations', 'LIST' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-perorg-child', 'Title', 1, 'TITLE', 'Related People and Organisations' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-perorg-child', 'Select', 5, 'SELECT',
 'SELECT *
    FROM awm_perorg_rel AS rel, awm_perorg AS person
    WHERE rel.perorg_id = %perorg_id%
      AND rel.perorg_rel_id = person.perorg_id
      AND rel.perorg_rel_type = ''%perorg_rel_type%''
 ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-perorg-child', 'Col-1', 110, 'COLUMN', 'perorg_id|ID #' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-perorg-child', 'Col-2', 120, 'COLUMN', 'perorg_name|Name' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-perorg-child', 'Col-3', 130, 'COLUMN', 'perorg_sort_key|Sort Key' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-perorg-child', 'Link-1', 131, 'LINK', '.*|view.php3?page=view-perorg&;save_actions=UPDATE&;perorg_id=%current_row->perorg_id%' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-perorg-child', 'Col-4', 140, 'COLUMN', 'perorg_rel_type|Relation' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-perorg-child', 'option-1', 200, 'OPTION', 'Add|view.php3?page=view-perorg&;save_actions=INSERT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'list-perorg-child', 'menu-1', 900, 'MENU', 'display' );



