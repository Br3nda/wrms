INSERT INTO awm_page ( page_name, page_desc, page_type )
 VALUES( 'view-user', 'View user record', 'VIEW' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'Title', 1, 'TITLE', 'User: ''%username%''' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'Access0', 2, 'ACCESS', '-99999|499|1000' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'Access1', 999, 'ACCESS', '500|999999|99999' );
INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'Access2', 1000, 'HTML', '<P>Unauthorised</P>' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'Select', 5,
  'SELECT', 'SELECT *, awm_get_perorg_data( awm_perorg.perorg_id, ''email'' ) AS email,
                       awm_get_rel_parent( awm_perorg.perorg_id, ''Employer'' ) AS org_code
   FROM awm_usr, awm_perorg WHERE username = ''%username%'' AND  awm_usr.perorg_id = awm_perorg.perorg_id' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'form-1', 100, 'FORM', 'save.php3?page=save-user&;save_actions=%save_actions%&;username=%username%" TARGET="help' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'in_username', 110, 'FIELD', 'username|User ID|TEXT|20' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'in_password', 112, 'FIELD', 'password|Password|PASSWORD|20' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'in_fullname', 120, 'FIELD', 'perorg_name|Full Name|TEXT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'in_email', 130, 'FIELD', 'email|E-Mail|TEXT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'in_enabled', 133, 'FIELD', 'enabled|Enabled|SELECT|10.yesno' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'in_validated', 136, 'FIELD', 'validated|Validated|SELECT|10.yesno' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'in_access_level', 138, 'FIELD', 'access_level|Access Level|INT' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'in_org_code', 140, 'FIELD',
   'org_code|Organisation|SELECT|awm_perorg.perorg_id, perorg_name
          FROM awm_perorg WHERE awm_perorg.perorg_type = ''O''|
          awm_perorg.perorg_id = (SELECT perorg_id FROM awm_perorg_rel
                      WHERE awm_perorg_rel.perorg_rel_type = ''Employer''
                        AND awm_perorg_rel.perorg_rel_id = %current_row->perorg_id%)' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'sel_userof[]', 150, 'FIELD', 'userof|Is A<BR>User<BR>Of<BR>&nbsp;|SELECT MULTI|system_code, system_desc FROM work_system|is_persys_role( ''%current_row->perorg_id%'', ''USER'', system_code) ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'sel_cltmgr[]', 160, 'FIELD', 'cltmgr|Organisational<BR>Contact For<BR>&nbsp;|SELECT MULTI|system_code, system_desc FROM work_system|is_persys_role( ''%current_row->perorg_id%'', ''CLTMGR'', system_code) ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'sel_sysmgr[]', 170, 'FIELD', 'sysmgr|System<BR>Maintainance<BR>Manager<BR>For<BR>&nbsp;|SELECT MULTI|system_code, system_desc FROM work_system|is_persys_role( ''%current_row->perorg_id%'', ''SYSMGR'', system_code) ' );

INSERT INTO awm_content ( page_name, content_name, content_seq, content_type, content_value )
 VALUES( 'view-user', 'Submit', 500, 'SUBMIT', '' );

