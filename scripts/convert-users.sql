--INSERT INTO usr (user_no, username, password, email_address, full_name)
--  SELECT DISTINCT ON user_no
--	awm_usr.perorg_id as user_no, username, password, 
--	awm_get_perorg_data(awm_usr.perorg_id,'email') AS email,
--	perorg_name AS full_name 
--	FROM awm_usr, awm_perorg 
--	WHERE awm_perorg.perorg_id=awm_usr.perorg_id;

INSERT INTO module VALUES('wrms','WRMS Module','1');
INSERT INTO ugroup VALUES(1,'wrms','Admin');
INSERT INTO ugroup VALUES(2,'wrms','Request');

INSERT INTO system_usr ( user_no, system_code, role )
   SELECT user_no, system_code, 'C' FROM usr, awm_usr, perorg_system
	       WHERE usr.username=awm_usr.username
				 AND awm_usr.perorg_id=perorg_system.perorg_id
				 AND perorg_system.persys_role='CLTMGR';

INSERT INTO system_usr ( user_no, system_code, role )
   SELECT user_no, system_code, 'E' FROM usr, awm_usr, perorg_system
	       WHERE usr.username=awm_usr.username
				 AND awm_usr.perorg_id=perorg_system.perorg_id
				 AND perorg_system.persys_role='USER';

INSERT INTO system_usr ( user_no, system_code, role )
   SELECT user_no, system_code, 'S' FROM usr, work_system
	       WHERE usr.username=work_system.notify_usr;

INSERT INTO org_usr ( user_no, org_code, role )
   SELECT user_no, org_code, 'C' FROM usr
	       WHERE usr.org_code=organisation.org_code AND organisation.admin_usr=usr.username;

UPDATE usr SET org_code=TEXT(awm_perorg_rel.perorg_id)
    WHERE usr.username=awm_usr.username
		  AND awm_perorg_rel.perorg_rel_id=awm_usr.perorg_id;


DELETE FROM lookup_code WHERE source_table='user' AND source_field='system_code';
INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_desc, lookup_misc)
   SELECT 'user', 'system_code', system_code, system_desc, notify_usr FROM work_system;
INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'user|system_code', 1, 'Systems');
DROP TABLE work_system;

DELETE FROM lookup_code WHERE source_table='request' AND source_field='status_code';
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc)
   SELECT 'request', 'status_code', status_code, status_desc, next_responsibility_is FROM status;
INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request|status_code', 1, 'Statuses');
DROP TABLE status;

DELETE FROM lookup_code WHERE source_table='request' AND source_field='severity_code';
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_seq, lookup_desc)
   SELECT 'request', 'severity_code', severity_code, severity_code, severity_desc FROM severity;
INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request|severity_code', 1, 'Severities');
DROP TABLE severity;

DELETE FROM lookup_code WHERE source_table='request' AND source_field='request_type';
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_seq, lookup_desc)
   SELECT 'request', 'request_type', request_type, request_type, request_type_desc FROM request_type;
INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request|request_type', 1, 'Request&nbsp;Types');
DROP TABLE request_type;

UPDATE request_allocated SET allocated_to_id=usr.user_no WHERE usr.username=request_allocated.allocated_to;
UPDATE request_interested SET user_no=usr.user_no WHERE usr.username=request_interested.username;
UPDATE request_note SET note_by_id=usr.user_no WHERE usr.username=request_note.note_by;
UPDATE request_quote SET quote_by_id=usr.user_no WHERE usr.username=request_quote.quoted_by;
UPDATE request_status SET status_by_id=usr.user_no WHERE usr.username=request_status.status_by;
UPDATE request_timesheet SET work_by_id=usr.user_no WHERE usr.username=request_timesheet.work_by;
UPDATE system_update SET update_by_id=usr.user_no WHERE usr.username=system_update.update_by;

INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'codes|menus', 999, 'Codes&nbsp;Tables');

UPDATE usr SET status='U' FROM awm_usr
  WHERE awm_usr.access_level < 5000 AND awm_usr.username=usr.username;

UPDATE usr SET status='C' FROM awm_usr
  WHERE awm_usr.access_level>=5000 AND awm_usr.username=usr.username;

UPDATE usr SET status='S' FROM awm_usr
  WHERE awm_usr.access_level>=10000 AND awm_usr.username=usr.username;

INSERT INTO group_member ( group_no, user_no )
  SELECT '1', user_no FROM awm_usr, usr
	        WHERE awm_usr.access_level>=10000 AND awm_usr.username=usr.username;

INSERT INTO group_member ( group_no, user_no )
  SELECT '2', user_no FROM awm_usr, usr
	        WHERE awm_usr.enabled>0 AND awm_usr.username=usr.username;

\i dump/t-session.sql

SELECT setval( 'usr_user_no_seq', max_usr() );
SELECT setval( 'ugroup_group_no_seq', max_group() );
SELECT setval( 'session_session_id_seq', max_session() );
