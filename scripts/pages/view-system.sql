INSERT INTO awm_page ( page_name, page_desc, page_type )
 VALUES( 'view-system', 'View System record', 'VIEW' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-system', 'Title', 1, 'TITLE', 'system: ''%system_code%''' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-system', 'Select', 5, 'SELECT', 'SELECT * FROM work_system WHERE system_code = ''%system_code%'' ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-system', 'form-1', 100, 'FORM', 'save.php3?page=save-system&;save_actions=%save_actions%&;system_code=%system_code%" TARGET="help' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-system', 'in_system_code', 110, 'FIELD', 'system_code|System Code|TEXT|20' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-system', 'in_system_desc', 120, 'FIELD', 'system_desc|Description|TEXT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-system', 'in_notify_usr', 130, 'FIELD', 'notify_usr|Notify User|SELECT|
   DISTINCT awm_usr.username, person.perorg_name
   FROM perorg_system AS persys, awm_perorg AS org, awm_perorg_rel, awm_perorg AS person, awm_usr
   WHERE persys.perorg_id = org.perorg_id
     AND persys.persys_role = ''SUPPORT''
     AND awm_perorg_rel.perorg_id = org.perorg_id
     AND awm_perorg_rel.perorg_rel_type = ''Employer''
     AND awm_perorg_rel.perorg_rel_id = person.perorg_id
     AND awm_usr.perorg_id = person.perorg_id
' );


INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-system', 'Submit', 500, 'SUBMIT', '' );
