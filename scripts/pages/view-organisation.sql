INSERT INTO awm_page ( page_name, page_desc, page_type )
 VALUES( 'view-organisation', 'View organisation record', 'VIEW' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-organisation', 'Title', 1, 'TITLE', 'organisation: ''%perorg_id%''' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-organisation', 'Select', 5,
  'SELECT', 'SELECT *
   FROM awm_perorg WHERE awm_perorg.perorg_id = %perorg_id%' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-organisation', 'form-1', 100, 'FORM', 'save.php3?page=save-organisation&;save_actions=%save_actions%&;perorg_id=%perorg_id%" TARGET="help' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-organisation', 'in_perorg_name', 110, 'FIELD', 'perorg_name|Name|TEXT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-organisation', 'in_perorg_sort_key', 120, 'FIELD', 'perorg_sort_key|Sort Name|TEXT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-organisation', 'sel_userof[]', 150, 'FIELD', 'userof|Is A<BR>User<BR>Of<BR>&nbsp;|SELECT MULTI|system_code, system_desc FROM work_system|is_persys_role( %perorg_id%, ''USER'', system_code) ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-organisation', 'sel_support[]', 160, 'FIELD', 'support|Provides<BR>Support<BR>For<BR>&nbsp;|SELECT MULTI|system_code, system_desc FROM work_system|is_persys_role( %perorg_id%, ''SUPPORT'', system_code) ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-organisation', 'Submit', 500, 'SUBMIT', '' );

