SELECT * INTO temp1 FROM awm_usr;
DELETE FROM temp1 WHERE temp1.username=usr.username OR temp1.username='scoop';
INSERT INTO usr (username, password, email, fullname)
  SELECT DISTINCT ON user_no
	username, password, 
	awm_get_perorg_data(temp1.perorg_id,'email') AS email,
	perorg_name AS full_name 
	FROM temp1, awm_perorg 
	WHERE awm_perorg.perorg_id=temp1.perorg_id;
DROP TABLE temp1;
UPDATE usr SET username=LOWER(username);
UPDATE work_system SET notify_usr=LOWER(notify_usr);
UPDATE organisation SET admin_usr=LOWER(admin_usr);

INSERT INTO module VALUES('wrms','WRMS Module','1');

INSERT INTO system_usr ( user_no, system_code, role )
   SELECT user_no, system_code, 'C' FROM usr, awm_usr, perorg_system
	       WHERE usr.username=LOWER(awm_usr.username)
				 AND awm_usr.perorg_id=perorg_system.perorg_id
				 AND perorg_system.persys_role='CLTMGR';

INSERT INTO system_usr ( user_no, system_code, role )
   SELECT user_no, system_code, 'E' FROM usr, awm_usr, perorg_system
	       WHERE usr.username=LOWER(awm_usr.username)
				 AND awm_usr.perorg_id=perorg_system.perorg_id
				 AND perorg_system.persys_role='USER';

INSERT INTO system_usr ( user_no, system_code, role )
   SELECT user_no, system_code, 'S' FROM usr, work_system
	       WHERE usr.username=work_system.notify_usr;

INSERT INTO org_usr ( user_no, org_code, role )
   SELECT user_no, org_code, 'C' FROM usr
	       WHERE usr.org_code=organisation.org_code AND organisation.admin_usr=usr.username;

UPDATE usr SET org_code=TEXT(awm_perorg_rel.perorg_id)
    WHERE usr.username=LOWER(awm_usr.username)
		  AND awm_perorg_rel.perorg_rel_id=awm_usr.perorg_id;


--DELETE FROM lookup_code WHERE source_table='user' AND source_field='system_code';
--INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_desc, lookup_misc)
--   SELECT 'user', 'system_code', system_code, system_desc, notify_usr FROM work_system;
--INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
--    VALUES('codes', 'menus', 'user|system_code', 1, 'Systems');
--DROP TABLE work_system;


DROP FUNCTION get_status_desc(CHAR);
CREATE FUNCTION get_status_desc(CHAR)
    RETURNS TEXT
    AS 'SELECT lookup_desc AS status_desc FROM lookup_code
            WHERE source_table=''request'' AND source_field=''status_code''
						AND lower(lookup_code) = lower($1)'
    LANGUAGE 'sql';

CREATE SEQUENCE temp1_seq;
DELETE FROM lookup_code WHERE source_table='request' AND source_field='status_code';
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc, lookup_misc)
   SELECT 'request', 'status_code', nextval('temp1_seq'), status_code, status_desc, next_responsibility_is FROM status;
INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request|status_code', 1, 'Status');
DROP TABLE status;
DROP SEQUENCE temp1_Seq;

DELETE FROM lookup_code WHERE source_table='request' AND source_field='severity_code';
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_seq, lookup_desc)
   SELECT 'request', 'severity_code', severity_code, severity_code, severity_desc FROM severity;
INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request|severity_code', 2, 'Severity');
DROP TABLE severity;

DELETE FROM lookup_code WHERE source_table='request' AND source_field='request_type';
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_seq, lookup_desc)
   SELECT 'request', 'request_type', request_type, request_type, request_type_desc FROM request_type;
INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request|request_type', 3, 'Request&nbsp;Type');
DROP TABLE request_type;

INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request|urgency', 4, 'Urgency');
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'urgency', 0, '0', 'Anytime' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'urgency', 1, '10', 'Sometime soon' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'urgency', 2, '20', 'Before Specified Date' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'urgency', 3, '30', 'On Specified Date' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'urgency', 4, '40', 'After Specified Date' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'urgency', 5, '50', 'As Soon As Possible' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'urgency', 6, '60', '''Yesterday''' );

ALTER TABLE request ADD COLUMN urgency INT;
UPDATE request SET urgency=(severity_code/10)*10;

INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request|importance', 5, 'Importance');
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'importance', 0, '0', 'Minor importance' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'importance', 1, '10', 'Average importance' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'importance', 2, '20', 'Major importance' );
INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
    VALUES( 'request', 'importance', 3, '30', 'Critical!' );

ALTER TABLE request ADD COLUMN importance INT;
UPDATE request SET importance=(severity_code/20)*10;


-- Need to rebuild the request_history table since it inherits from
-- request.  One of the disadvantages of objects :-( well, implementation
-- probably, in reality :-)
DROP TABLE request_history;
CREATE TABLE request_history (
  modified_on DATETIME DEFAULT TEXT 'now'
) INHERITS (request );
GRANT INSERT,SELECT ON request_history TO PUBLIC;
CREATE INDEX xpk_request_history ON request_history ( request_id, modified_on );
\i dump/t-request_history.sql
UPDATE request_history SET urgency=(severity_code/10)*10;
UPDATE request_history SET importance=(severity_code/20)*10;


INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request_quote|quote_units', 6, 'Quote&nbsp;Unit');
INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'request_quote|quote_type', 7, 'Quote&nbsp;Type');


UPDATE request SET requester_id=usr.user_no WHERE usr.username=LOWER(request.request_by);
UPDATE request_history SET requester_id=usr.user_no WHERE usr.username=LOWER(request_history.request_by);
UPDATE request_note SET note_by_id=usr.user_no WHERE usr.username=LOWER(request_note.note_by);
UPDATE request_quote SET quote_by_id=usr.user_no WHERE usr.username=LOWER(request_quote.quoted_by);
UPDATE request_status SET status_by_id=usr.user_no WHERE usr.username=LOWER(request_status.status_by);
UPDATE request_timesheet SET work_by_id=usr.user_no WHERE usr.username=LOWER(request_timesheet.work_by);
UPDATE system_update SET update_by_id=usr.user_no WHERE usr.username=LOWER(system_update.update_by);

-- Add a column to say when something about the request was last changed
-- and populate it with reasonable data...
ALTER TABLE request ADD COLUMN last_activity DATETIME DEFAULT text('-infinity');
UPDATE request SET last_activity=modified_on FROM request, request_history
  WHERE request.request_id=request_history.request_id;
UPDATE request SET last_activity=status_on FROM request, request_status
  WHERE request.request_id=request_status.request_id
  AND (request_status.status_on>last_activity OR last_activity IS NULL);
UPDATE request SET last_activity=note_on FROM request, request_note
  WHERE request.request_id=request_note.request_id
  AND (request_note.note_on>last_activity OR last_activity IS NULL);
UPDATE request SET last_activity=quoted_on FROM request, request_quote
  WHERE request.request_id=request_quote.request_id
  AND (request_quote.quoted_on>last_activity OR last_activity IS NULL);
UPDATE request SET last_activity=work_on FROM request, request_timesheet
  WHERE request.request_id=request_timesheet.request_id
  AND (request_timesheet.work_on>last_activity OR last_activity IS NULL);
UPDATE request SET last_activity=update_on FROM request, request_update, system_update
  WHERE request.request_id=request_update.request_id
  AND system_update.update_id=request_update.update_id
  AND (system_update.update_on>last_activity OR last_activity IS NULL);


DELETE FROM request_allocated;
INSERT INTO request_allocated (request_id, allocated_on, allocated_to )
    SELECT request_id, perreq_from, username FROM perorg_request, awm_usr
		          WHERE awm_usr.perorg_id=perorg_request.perorg_id
							AND perreq_role='ALLOC';
UPDATE request_allocated SET allocated_to_id=usr.user_no WHERE usr.username=request_allocated.allocated_to;

DELETE FROM request_interested;
INSERT INTO request_interested (request_id, username )
    SELECT request_id, username FROM perorg_request, awm_usr
		          WHERE awm_usr.perorg_id=perorg_request.perorg_id
							AND perreq_role='INTRST';
UPDATE request_interested SET user_no=usr.user_no WHERE usr.username=request_interested.username;

ALTER TABLE organisation ADD COLUMN abbreviation TEXT;
UPDATE organisation SET abbreviation=awm_perorg.perorg_sort_key
    WHERE text(awm_perorg.perorg_id)=organisation.org_code;
UPDATE org_system SET org_code=organisation.org_code
    WHERE organisation.abbreviation=org_system.org_code;

INSERT INTO lookup_code (source_table, source_field, lookup_code, lookup_seq, lookup_desc )
    VALUES('codes', 'menus', 'codes|menus', 999, 'Codes&nbsp;Tables');

UPDATE usr SET status='U' FROM awm_usr
  WHERE awm_usr.access_level < 5000 AND LOWER(awm_usr.username)=usr.username;

UPDATE usr SET status='C' FROM awm_usr
  WHERE awm_usr.access_level>=5000 AND LOWER(awm_usr.username)=usr.username;

UPDATE usr SET status='S' FROM awm_usr
  WHERE awm_usr.access_level>=10000 AND LOWER(awm_usr.username)=usr.username;


INSERT INTO ugroup VALUES(1,'wrms','Admin');
INSERT INTO ugroup VALUES(2,'wrms','Support');
INSERT INTO ugroup VALUES(3,'wrms','Manage');
INSERT INTO ugroup VALUES(4,'wrms','Request');

INSERT INTO group_member ( group_no, user_no )
  SELECT '1', user_no FROM awm_usr, usr
	        WHERE awm_usr.access_level>=10000 AND LOWER(awm_usr.username)=usr.username;

INSERT INTO group_member ( group_no, user_no )
  SELECT '2', user_no FROM usr
	        WHERE usr.status = 'S';

INSERT INTO group_member ( group_no, user_no )
  SELECT '3', user_no FROM usr
	        WHERE usr.status = 'S' OR usr.status = 'C';

INSERT INTO group_member ( group_no, user_no )
  SELECT '4', user_no FROM awm_usr, usr
	        WHERE awm_usr.enabled>0 AND LOWER(awm_usr.username)=usr.username;


\i dump/t-session.sql

CREATE FUNCTION set_interested (int4, int4) RETURNS int4 AS '
   DECLARE
      u_no ALIAS FOR $1;
      req_id ALIAS FOR $2;
      curr_val TEXT;
   BEGIN
      SELECT username INTO curr_val FROM request_interested
			                WHERE user_no = u_no AND request_id = req_id;
      IF NOT FOUND THEN
        INSERT INTO request_interested (user_no, request_id, username)
				    SELECT user_no, req_id, username FROM usr WHERE user_no = u_no;
      END IF;
      RETURN u_no;
   END;
' LANGUAGE 'plpgsql';

CREATE FUNCTION set_allocated (int4, int4) RETURNS int4 AS '
   DECLARE
      u_no ALIAS FOR $1;
      req_id ALIAS FOR $2;
      curr_val TEXT;
   BEGIN
      SELECT username INTO curr_val FROM request_allocated
			                WHERE user_no = u_no AND request_id = req_id;
      IF NOT FOUND THEN
        INSERT INTO request_allocated (user_no, request_id, username)
				    SELECT user_no, req_id, username FROM usr WHERE user_no = u_no;
      END IF;
      RETURN u_no;
   END;
' LANGUAGE 'plpgsql';
DROP FUNCTION set_perreq_role(int4,int4,text);

-- CREATE request_fti ( string TEXT, id OID );
-- CREATE FUNCTION fti() RETURNS OPAUQE AS '/usr/lib/postgresql/modules/fti.so' LANGUAGE 'C';
-- CREATE TRIGGER request_fti_trigger AFTER UPDATE or INSERT or DELETE ON request
--     FOR EACH ROW EXECUTE PROCEDURE fti( request_fti, detailed);

SELECT setval( 'usr_user_no_seq', max_usr() );
SELECT setval( 'ugroup_group_no_seq', max_group() );
SELECT setval( 'session_session_id_seq', max_session() );
