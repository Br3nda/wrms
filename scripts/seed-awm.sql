INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc )
 VALUES( 'awm_perorg', 'perorg_type', 'P', 'Person' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
 VALUES( 'awm_perorg', 'perorg_type', 10, 'O', 'Organisation' );

INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
 VALUES( '10', 'yesno', 0, '0', 'No' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
 VALUES( '10', 'yesno', 1, '1', 'Yes' );

INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_perorg_rel', 'perorg_rel_type', 'Employee', 'Person is an employee of organisation', 'Employer' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_perorg_rel', 'perorg_rel_type', 'Spouse', 'Person is married to another person', 'Spouse' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_perorg_rel', 'perorg_rel_type', 'Child', 'Person is a descendant of another person', 'Parent' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_perorg_rel', 'perorg_rel_type', 'Parent', 'Person is parent of another person', 'Child' );

INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_perorg_data', 'po_data_name', 'email', 'Primary e-mail address', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_perorg_data', 'po_data_name', 'mobile', 'Mobile phone number', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_perorg_data', 'po_data_name', 'website', 'Primary website address', '' );

INSERT INTO awm_perorg ( perorg_name, perorg_sort_key, perorg_type )
 VALUES( 'Catalyst IT Ltd', 'Catalyst IT Ltd', 'O');
INSERT INTO awm_perorg ( perorg_name, perorg_sort_key, perorg_type )
 VALUES( 'Andrew McMillan', 'McMillan, Andrew', 'P');
INSERT INTO awm_perorg ( perorg_name, perorg_sort_key, perorg_type )
 VALUES( 'Mandy Natusch', 'Natusch, Mandy', 'P');
INSERT INTO awm_perorg ( perorg_name, perorg_sort_key, perorg_type )
 VALUES( 'Heather Buchanan', 'Buchanan, Heather', 'P');
INSERT INTO awm_perorg ( perorg_name, perorg_sort_key, perorg_type )
 VALUES( 'Max McMillan', 'McMillan, Max', 'P');

INSERT INTO awm_perorg_rel ( perorg_id, perorg_rel_type, perorg_rel_id )
 VALUES( awm_perorg_id_from_name( 'Andrew McMillan'), 'Employee', awm_perorg_id_from_name( 'Catalyst IT Ltd') );
INSERT INTO awm_perorg_rel ( perorg_id, perorg_rel_type, perorg_rel_id )
 VALUES( awm_perorg_id_from_name( 'Catalyst IT Ltd'), 'Employer', awm_perorg_id_from_name( 'Andrew McMillan') );
INSERT INTO awm_perorg_rel ( perorg_id, perorg_rel_type, perorg_rel_id )
 VALUES( awm_perorg_id_from_name( 'Heather Buchanan'), 'Spouse', awm_perorg_id_from_name( 'Andrew McMillan') );
INSERT INTO awm_perorg_rel ( perorg_id, perorg_rel_type, perorg_rel_id )
 VALUES( awm_perorg_id_from_name( 'Max McMillan'), 'Child', awm_perorg_id_from_name( 'Andrew McMillan') );
INSERT INTO awm_perorg_rel ( perorg_id, perorg_rel_type, perorg_rel_id )
 VALUES( awm_perorg_id_from_name( 'Max McMillan'), 'Child', awm_perorg_id_from_name( 'Heather Buchanan') );
INSERT INTO awm_perorg_rel ( perorg_id, perorg_rel_type, perorg_rel_id )
 VALUES( awm_perorg_id_from_name( 'Andrew McMillan'), 'Spouse', awm_perorg_id_from_name( 'Heather Buchanan') );
INSERT INTO awm_perorg_rel ( perorg_id, perorg_rel_type, perorg_rel_id )
 VALUES( awm_perorg_id_from_name( 'Heather Buchanan'), 'Parent', awm_perorg_id_from_name( 'Max McMillan') );
INSERT INTO awm_perorg_rel ( perorg_id, perorg_rel_type, perorg_rel_id )
 VALUES( awm_perorg_id_from_name( 'Andrew McMillan'), 'Parent', awm_perorg_id_from_name( 'Max McMillan') );

INSERT INTO awm_perorg_data ( perorg_id, po_data_name, po_data_value )
 VALUES( awm_perorg_id_from_name( 'Andrew McMillan'), 'email', 'Andrew@cat-it.co.nz' );
INSERT INTO awm_perorg_data ( perorg_id, po_data_name, po_data_value )
 VALUES( awm_perorg_id_from_name( 'Andrew McMillan'), 'mobile', '+64 (21) 635 694' );
INSERT INTO awm_perorg_data ( perorg_id, po_data_name, po_data_value )
 VALUES( awm_perorg_id_from_name( 'Andrew McMillan'), 'website', 'http://www.cat-it.co.nz/~andrew/' );

INSERT INTO awm_perorg_data ( perorg_id, po_data_name, po_data_value )
 VALUES( awm_perorg_id_from_name( 'Mandy Natusch'), 'email', 'mandy@parentscentre.org.nz' );

INSERT INTO awm_usr ( username, password, validated, enabled, access_level, perorg_id)
 VALUES ('andrew', '4thrite', 1, 1, 99999, awm_perorg_id_from_name( 'Andrew McMillan') );
INSERT INTO awm_usr ( username, password, validated, enabled, access_level, perorg_id)
 VALUES ('mandy', 'natusch', 1, 1, 500, awm_perorg_id_from_name( 'Mandy Natusch') );

INSERT INTO awm_group ( group_name, group_desc ) VALUES( 'everyone', 'All Users' );
INSERT INTO awm_group ( group_name, group_desc ) VALUES( 'centre', 'Centre Users' );
INSERT INTO awm_group ( group_name, group_desc ) VALUES( 'sysadmin', 'System Administration Users' );

INSERT INTO awm_usr_group( username, group_name ) VALUES( 'andrew', 'everyone' );
INSERT INTO awm_usr_group( username, group_name ) VALUES( 'andrew', 'wrmsadmin' );
INSERT INTO awm_usr_group( username, group_name ) VALUES( 'andrew', 'siteadmin' );
INSERT INTO awm_usr_group( username, group_name ) VALUES( 'andrew', 'useradmin' );
INSERT INTO awm_usr_group( username, group_name ) VALUES( 'andrew', 'systemadmin' );
INSERT INTO awm_usr_group( username, group_name ) VALUES( 'mandy', 'everyone' );
INSERT INTO awm_usr_group( username, group_name ) VALUES( 'mandy', 'systemadmin' );
INSERT INTO awm_usr_group( username, group_name ) VALUES( 'mandy', 'siteadmin' );

INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_page', 'page_type', 'LIST', 'Page which lists records', 'view.php3' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_page', 'page_type', 'VIEW', 'Page which displays a record', 'view.php3' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_page', 'page_type', 'SAVE', 'Page which saves a record', 'save.php3' );

INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'TITLE', 'Page title', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'HTML', 'Raw HTML to insert into page', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'SELECT', 'SELECT statement template', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'INSERT', 'INSERT statement template', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'UPDATE', 'UPDATE statement template', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'TABLE', 'TABLE insert/update/delete statement template', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'DELETE', 'DELETE statement template', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'COLUMN', 'Listing column', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'LINK', 'LINK - Pattern and link template for a column', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'FORM', 'FORM - Open a sequence of ''FIELD'' records', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'FIELD', 'FIELD - Textarea, Fill-in or selection field', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'SUBMIT', 'SUBMIT - Completes a ''FORM'' sequence of fields', '' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'awm_content', 'content_type', 'MENU', 'Page menu', '' );

INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'request_quote', 'quote_type', 'Q', 'Quote', '');
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'request_quote', 'quote_type', 'E', 'Estimate', '');
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'request_quote', 'quote_type', 'G', 'Guess', '');
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'request_quote', 'quote_type', 'B', 'Ballpark', '');

INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'request_quote', 'quote_units', 'hours', 'Hours', '');
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'request_quote', 'quote_units', 'days', 'Days', '');
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'request_quote', 'quote_units', 'dollars', 'Dollars', '');

INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'status', 'next_responsibility_is', 'Client', 'Awaiting action/decision from client', '');
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
 VALUES( 'status', 'next_responsibility_is', 'Support', 'Awaiting action/decision from support', '');
