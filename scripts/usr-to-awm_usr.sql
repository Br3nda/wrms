DELETE FROM awm_perorg_data;
DELETE FROM awm_perorg;
DELETE FROM awm_perorg_rel;
DELETE FROM awm_usr;
SELECT setval( 'awm_perorg_perorg_id_seq', 1 );

DELETE FROM usr WHERE username = 'billgilbert';

INSERT INTO awm_perorg (perorg_name, perorg_sort_key, perorg_type)
   SELECT fullname, fullname, 'P' FROM usr;

INSERT INTO awm_perorg_data (perorg_id, po_data_name, po_data_value)
   SELECT awm_perorg_id_from_name(usr.fullname), 'email', usr.email FROM usr;

INSERT INTO awm_usr (username, password, validated, enabled, access_level, perorg_id, last_accessed)
   SELECT username, password, validated, enabled, (access_level * 100), awm_perorg_id_from_name(usr.fullname), last_accessed FROM usr;


INSERT INTO awm_perorg (perorg_name, perorg_sort_key, perorg_type)
   SELECT org_name, org_code, 'O' FROM organisation;

INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc )
   VALUES( 'awm_perorg_rel', 'perorg_rel_type', 'primary', 'Primary Contact' );

INSERT INTO awm_perorg_rel (perorg_id, perorg_rel_id, perorg_rel_type)
   SELECT awm_perorg_id_from_name(organisation.org_name),
          awm_perorg_id_from_name(usr.fullname),
          'admin_usr' FROM usr, organisation WHERE organisation.admin_usr = usr.username;

INSERT INTO awm_perorg_rel (perorg_id, perorg_rel_id, perorg_rel_type)
   SELECT awm_perorg_id_from_name(organisation.org_name),
          awm_perorg_id_from_name(usr.fullname),
          'Employer' FROM usr, organisation WHERE organisation.org_code = usr.org_code;


INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc )
   VALUES( 'perorg_system', 'persys_role', 'SYSMGR', 'System Manager' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc )
   VALUES( 'perorg_system', 'persys_role', 'SUPPORT', 'Support Person' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc )
   VALUES( 'perorg_system', 'persys_role', 'USER', 'System User' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc )
   VALUES( 'perorg_system', 'persys_role', 'CLTADM', 'Client System Administrator' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc )
   VALUES( 'perorg_system', 'persys_role', 'CLTMGR', 'Client Manager/Approver' );

CREATE TABLE perorg_system (
   perorg_id INT4,
   persys_role TEXT,
   system_code TEXT
) ;
GRANT INSERT,UPDATE,SELECT ON perorg_system TO general;
CREATE UNIQUE INDEX perorg_system_key ON perorg_system ( perorg_id, persys_role, system_code );
CREATE INDEX perorg_system_ak1 ON perorg_system ( system_code, persys_role );

CREATE FUNCTION is_persys_role( INT4, TEXT, TEXT ) RETURNS BOOLEAN AS '
   DECLARE
      answer BOOLEAN;
      unused INT4;
   BEGIN
      SELECT perorg_id INTO unused FROM perorg_system
                 WHERE perorg_id = $1 AND persys_role = $2 AND system_code = $3;
      IF FOUND THEN
         answer = TRUE;
      ELSE
         answer = FALSE;
      END IF;
      RETURN answer;
   END;
' LANGUAGE 'plpgsql';


INSERT INTO perorg_system ( perorg_id, persys_role, system_code)
   SELECT awm_perorg_id_from_name(usr.fullname), 'SYSMGR'::text, work_system.system_code
         FROM work_system, usr
         WHERE work_system.notify_usr = usr.username;

INSERT INTO perorg_system ( perorg_id, persys_role, system_code)
   SELECT awm_perorg_id_from_name(usr.fullname), 'CLTMGR'::text, org_system.system_code
         FROM org_system, organisation, usr
         WHERE organisation.admin_usr = usr.username
           AND organisation.org_code = org_system.org_code;

INSERT INTO perorg_system ( perorg_id, persys_role, system_code)
   SELECT awm_perorg_id_from_name(usr.fullname), 'USER'::text, org_system.system_code
         FROM org_system, organisation, usr
         WHERE organisation.org_code = usr.org_code
           AND organisation.org_code = org_system.org_code
           AND organisation.org_code != 'CATALYST';


INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
   SELECT 'request'::text, 'request_type'::text,
         request_type, text(request_type), request_type_desc
         FROM request_type ;

INSERT INTO lookup_code ( source_table, source_field, lookup_seq, lookup_code, lookup_desc )
   SELECT 'request'::text, 'severity_code'::text,
         severity_code, text(severity_code), severity_desc
         FROM severity ;

INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc, lookup_misc )
   SELECT 'request_status'::text, 'status_code'::text,
         text(status_code), status_desc, next_responsibility_is
         FROM status ;

ALTER TABLE request ADD COLUMN requester_id INT4;
UPDATE request SET requester_id = awm_usr.perorg_id
               FROM awm_usr, request
               WHERE awm_usr.username = request.request_by;

ALTER TABLE request_quote ADD COLUMN quote_by_id INT4;
UPDATE request_quote SET quote_by_id = awm_usr.perorg_id
               FROM awm_usr, request_quote
               WHERE awm_usr.username = request_quote.quoted_by;

ALTER TABLE request_note ADD COLUMN note_by_id INT4;
UPDATE request_note SET note_by_id = awm_usr.perorg_id
               FROM awm_usr, request_note
               WHERE awm_usr.username = request_note.note_by;

ALTER TABLE request_status ADD COLUMN status_by_id INT4;
UPDATE request_status SET status_by_id = awm_usr.perorg_id
               FROM awm_usr, request_status
               WHERE awm_usr.username = request_status.status_by;

ALTER TABLE system_update ADD COLUMN update_by_id INT4;
UPDATE system_update SET update_by_id = awm_usr.perorg_id
               FROM awm_usr, system_update
               WHERE awm_usr.username = system_update.update_by;

ALTER TABLE request_timesheet ADD COLUMN work_by_id INT4;
UPDATE request_timesheet SET work_by_id = awm_usr.perorg_id
               FROM awm_usr, request_timesheet
               WHERE awm_usr.username = request_timesheet.work_by;


INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc )
   VALUES( 'perorg_request', 'perreq_role', 'ALLOC', 'Allocated To' );
INSERT INTO lookup_code ( source_table, source_field, lookup_code, lookup_desc )
   VALUES( 'perorg_request', 'perreq_role', 'INTRST', 'Interested In' );

CREATE TABLE perorg_request (
   perorg_id INT4,
   request_id INT4,
   perreq_from DATETIME DEFAULT TEXT 'now',
   perreq_role TEXT
) ;
GRANT INSERT,UPDATE,SELECT ON perorg_request TO general;
CREATE UNIQUE INDEX perorg_request_pkey ON perorg_request ( perorg_id, perreq_role, request_id );
CREATE INDEX perorg_system_ak1 ON perorg_system ( request_id, perreq_role );

INSERT INTO perorg_request ( perorg_id, request_id, perreq_from, perreq_role )
   SELECT awm_usr.perorg_id, request_allocated.request_id, allocated_on, 'ALLOC'::text
          FROM request_allocated, awm_usr
          WHERE awm_usr.username = request_allocated.allocated_to ;

INSERT INTO perorg_request ( perorg_id, request_id, perreq_role )
   SELECT awm_usr.perorg_id, request_interested.request_id, 'INTRST'::text
          FROM request_interested, awm_usr
          WHERE awm_usr.username = request_interested.username ;

INSERT INTO awm_usr_setting ( username, setting_name, setting_value )
   SELECT username, setting_name, setting_value FROM usr_setting;

UPDATE usr SET org_code = perorg_id FROM usr, awm_perorg WHERE usr.org_code = perorg_sort_key;

UPDATE organisation SET org_code = awm_perorg_id_from_name( org_name );

INSERT INTO perorg_system ( perorg_id, persys_role, system_code)
  SELECT perorg_id, 'USER'::text, system_code 
         FROM organisation, org_system, awm_perorg 
         WHERE org_system.org_code = perorg_sort_key AND text(perorg_id) = organisation.org_code;



UPDATE awm_usr SET username = lower(username);
UDPATE organisation SET admin_usr = LOWER(admin_usr);
UPDATE work_system SET notify_usr = LOWER(notify_usr);
UPDATE usr SET username = lower(username);

DROP FUNCTION get_usr_email(TEXT);
CREATE FUNCTION get_usr_email(TEXT)
    RETURNS TEXT
    AS 'SELECT po_data_value FROM awm_perorg_data, awm_usr
           WHERE LOWER(awm_usr.username) = LOWER($1)
             AND awm_perorg_data.perorg_id = awm_usr.perorg_id
             AND po_data_name = ''email'' '
    LANGUAGE 'sql';


CREATE TABLE request_history (
  modified_on DATETIME DEFAULT TEXT 'now'
) INHERITS ( request );
GRANT INSERT,SELECT ON request_history TO PUBLIC;
CREATE INDEX xpk_request_history ON request_history ( request_id, modified_on );

INSERT INTO request_history SELECT * FROM request_history2;

CREATE FUNCTION set_perreq_role (int4, int4, text) RETURNS text AS '
   DECLARE
      po_id ALIAS FOR $1;
      req_id ALIAS FOR $2;
      role_code ALIAS FOR $3;
      curr_val TEXT;
   BEGIN
      SELECT perreq_role INTO curr_val FROM perorg_request WHERE perorg_id = po_id AND request_id = req_id AND perreq_role = role_code;
      IF NOT FOUND THEN
        INSERT INTO perorg_request (perorg_id, request_id, perreq_role) VALUES( po_id, req_id, role_code);
      END IF;
      RETURN role_code;
   END;
' LANGUAGE 'plpgsql';

VACUUM VERBOSE ANALYZE;

