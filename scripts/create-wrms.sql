CREATE TABLE usr (
    user_no SERIAL,
    validated INT2 DEFAULT 0,
    enabled INT2 DEFAULT 1,
    access_level INT4 DEFAULT 10,
    last_accessed DATETIME,
    linked_user INT4,
    username TEXT NOT NULL UNIQUE,
    password TEXT,
    email TEXT,
    fullname TEXT,
    joined DATETIME DEFAULT TEXT 'now',
    last_update DATETIME,
    status CHAR,
    phone TEXT,
    fax TEXT,
		pager TEXT,
    org_code INT4,
    organisation TEXT,
		email_ok BOOL DEFAULT TRUE,
		pager_ok BOOL DEFAULT TRUE,
		phone_ok BOOL DEFAULT TRUE,
		fax_ok   BOOL DEFAULT TRUE,
    mail_style CHAR,
		note CHAR );
GRANT SELECT,INSERT,UPDATE ON usr TO PUBLIC;
GRANT ALL ON usr TO andrew;
CREATE FUNCTION max_usr() RETURNS INT4 AS 'SELECT max(user_no) FROM usr' LANGUAGE 'sql';
GRANT SELECT,UPDATE ON usr_user_no_seq TO general;
GRANT ALL ON usr_user_no_seq TO andrew;
CREATE INDEX xak1_usr ON usr ( org_code, username );

CREATE TABLE usr_setting (
  username TEXT,
  setting_name TEXT,
  setting_value TEXT
);
GRANT INSERT,UPDATE,SELECT ON usr_setting TO general;
CREATE INDEX xpk_usr_setting ON usr_setting ( username, setting_name );

CREATE FUNCTION get_usr_setting(TEXT,TEXT)
    RETURNS TEXT
    AS 'SELECT setting_value FROM usr_setting
            WHERE usr_setting.username = $1
            AND usr_setting.setting_name = $2'
    LANGUAGE 'sql';


CREATE TABLE organisation (
  org_code SERIAL PRIMARY KEY,
	active BOOL DEFAULT TRUE,
	debtor_no INT4,
	work_rate FLOAT,
	abbreviation TEXT,
  org_name TEXT,
  admin_usr TEXT
) ;
GRANT SELECT ON organisation TO PUBLIC;
GRANT INSERT,UPDATE,SELECT ON organisation TO general;
CREATE FUNCTION max_organisation() RETURNS INT4 AS 'SELECT max(org_code) FROM organisation' LANGUAGE 'sql';
GRANT SELECT,UPDATE ON organisation_org_code_seq TO general;


CREATE TABLE request (
  request_id SERIAL PRIMARY KEY,
  request_on DATETIME DEFAULT TEXT 'now',
  active BOOL DEFAULT TRUE,
  last_status CHAR DEFAULT 'N',
	urgency INT2,
	importance INT2,
  severity_code INT2,
  request_type INT2,
  requester_id INT4,
  eta DATETIME,
  last_activity DATETIME DEFAULT TEXT 'now',
  request_by TEXT,
  brief TEXT,
  detailed TEXT,
  system_code TEXT
) ;
CREATE INDEX xak0_request ON request ( active int4_ops, request_id );
CREATE INDEX xak1_request ON request ( active int4_ops, severity_code );
CREATE INDEX xak2_request ON request ( active int4_ops, severity_code, request_by );
CREATE INDEX xak3_request ON request ( active int4_ops, request_by );
CREATE INDEX xak4_request ON request ( active int4_ops, last_status );
GRANT INSERT,UPDATE,SELECT ON request TO general;
GRANT SELECT,UPDATE ON request_request_id_seq TO PUBLIC;


CREATE TABLE request_words ( string TEXT, id OID );
CREATE FUNCTION keyword() RETURNS OPAQUE AS '/usr/lib/postgresql/modules/keyword.so' LANGUAGE 'C';
CREATE TRIGGER request_kidx_trigger AFTER UPDATE or INSERT or DELETE ON request
     FOR EACH ROW EXECUTE PROCEDURE keyword( request_words, detailed);
GRANT DELETE,INSERT,UPDATE,SELECT ON request_words TO general;


CREATE FUNCTION active_request(INT4)
    RETURNS BOOL
    AS 'SELECT active FROM request WHERE request.request_id = $1'
    LANGUAGE 'sql';
CREATE FUNCTION max_request()
    RETURNS INT4
    AS 'SELECT max(request_id) FROM request'
    LANGUAGE 'sql';
CREATE FUNCTION get_request_org(INT4)
    RETURNS INT4
    AS 'SELECT usr.org_code FROM request, usr WHERE request.request_id = $1 AND request.request_by = usr.username'
    LANGUAGE 'sql';

CREATE TABLE work_system (
  system_code TEXT NOT NULL UNIQUE PRIMARY KEY,
  system_desc TEXT,
	active BOOL,
  notify_usr TEXT
) ;
GRANT SELECT ON work_system TO PUBLIC;
GRANT INSERT,UPDATE,SELECT ON work_system TO general;

CREATE TABLE org_system (
  org_code INT4 NOT NULL,
  system_code TEXT
) ;
GRANT SELECT ON org_system TO PUBLIC;
GRANT DELETE,INSERT,UPDATE,SELECT ON org_system TO general;
CREATE INDEX xpk_org_system ON org_system ( org_code, system_code );
CREATE INDEX xak1_org_system ON org_system ( system_code, org_code );

CREATE TABLE request_status (
  request_id INT4,
  status_on DATETIME,
  status_by_id INT4,
  status_by TEXT,
  status_code TEXT
) ;
CREATE UNIQUE INDEX xpk_request_status ON request_status ( request_id, status_on );
GRANT INSERT,SELECT ON request_status TO PUBLIC;

CREATE TABLE request_quote (
  quote_id SERIAL PRIMARY KEY,
  request_id INT4,
  quoted_on DATETIME DEFAULT TEXT 'now',
  quote_amount FLOAT8,
  quote_by_id INT4,
  quoted_by TEXT,
  quote_type TEXT,
  quote_units TEXT,
  quote_brief TEXT,
  quote_details TEXT
);
CREATE INDEX xak1_request_quote ON request_quote ( request_id );
GRANT INSERT,UPDATE,SELECT ON request_quote TO general;
GRANT SELECT,UPDATE ON request_quote_quote_id_seq TO PUBLIC;

CREATE FUNCTION max_quote() RETURNS INT4 AS 'SELECT max(quote_id) FROM request_quote' LANGUAGE 'sql';


CREATE TABLE request_allocated (
  request_id INT4,
  allocated_on DATETIME DEFAULT TEXT 'now',
	allocated_to_id INT4,
  allocated_to TEXT
);
GRANT INSERT,UPDATE,SELECT ON request_allocated TO general;

CREATE TABLE request_timesheet (
  timesheet_id SERIAL PRIMARY KEY,
  request_id INT4,
  work_on DATETIME,
  ok_to_charge BOOL,
	work_quantity FLOAT8,
  work_duration INTERVAL,
  work_by_id INT4,
  work_by TEXT,
  work_description TEXT,
  work_rate FLOAT8,
  work_charged DATETIME,
	charged_amount FLOAT8,
	charged_by_id INT4,
	work_units TEXT,
	charged_details TEXT
);
CREATE INDEX request_timesheet_skey1 ON request_timesheet ( work_on, work_by_id, request_id );
CREATE INDEX request_timesheet_skey2 ON request_timesheet ( ok_to_charge, request_id );
GRANT INSERT,UPDATE,SELECT ON request_timesheet TO general;
CREATE FUNCTION max_timesheet() RETURNS INT4 AS 'SELECT max(timesheet_id) FROM request_timesheet' LANGUAGE 'sql';


CREATE TABLE request_note (
  request_id INT4,
  note_on DATETIME DEFAULT TEXT 'now',
  note_by_id INT4,
  note_by TEXT,
  note_detail TEXT,
	PRIMARY KEY ( request_id, note_on )
) ;
-- CREATE UNIQUE INDEX xpk_request_note ON request_note ( request_id, note_on );
GRANT INSERT,SELECT ON request_note TO PUBLIC;

CREATE FUNCTION get_last_note_on(INT4)
    RETURNS DATETIME
    AS 'SELECT max(note_on) FROM request_note WHERE request_note.request_id = $1'
    LANGUAGE 'sql';

CREATE TABLE request_interested (
  request_id INT4,
	user_no INT4 DEFAULT -1,
  username TEXT,
	PRIMARY KEY ( request_id, username )
) ;
-- CREATE UNIQUE INDEX xpk_request_interested ON request_interested ( request_id, username );
GRANT INSERT,SELECT,UPDATE,DELETE ON request_interested TO PUBLIC;

CREATE TABLE request_request (
  request_id INT4,
  to_request_id INT4,
  link_type CHAR,
  link_data TEXT,
	PRIMARY KEY ( request_id, link_type, to_request_id )
) ;
CREATE INDEX request_request_sk1 ON request_request ( to_request_id );
GRANT INSERT,SELECT,UPDATE,DELETE ON request_request TO PUBLIC;


CREATE TABLE request_history (
  modified_on DATETIME DEFAULT TEXT 'now'
) INHERITS (request );
GRANT INSERT,SELECT ON request_history TO PUBLIC;
CREATE INDEX xpk_request_history ON request_history ( request_id, modified_on );

---------------------------------------------------------------
-- Should replace this with a more generic 'associated file'
-- mechanism at some point in the future...
---------------------------------------------------------------
CREATE TABLE system_update (
  update_id SERIAL PRIMARY KEY,
  update_on DATETIME DEFAULT TEXT 'now',
  update_by_id INT4,
  update_by TEXT,
  update_brief TEXT,
  update_description TEXT,
  file_url TEXT,
  system_code TEXT
) ;
CREATE INDEX xak1_system_update ON system_update ( system_code, update_id );
GRANT INSERT,UPDATE,SELECT ON system_update TO general;
GRANT SELECT,UPDATE ON system_update_update_id_seq TO PUBLIC;

CREATE FUNCTION max_update() RETURNS INT4 AS 'SELECT max(update_id) FROM system_update' LANGUAGE 'sql';


CREATE TABLE request_update (
  request_id INT4,
  update_id INT4,
	PRIMARY KEY ( request_id, update_id )
);
-- CREATE INDEX xpk_request_update ON request_update ( request_id );
CREATE INDEX xak1_request_update ON request_update ( update_id );
GRANT INSERT,UPDATE,SELECT ON request_update TO general;

-- keep this in case we need it again!
-- Or better, just use enable_pgpl
--  DROP PROCEDURAL LANGUAGE 'plpgsql';
--  CREATE FUNCTION plpgsql_call_handler ()
--         RETURNS OPAQUE AS '/usr/lib/postgresql/lib/plpgsql.so'
--         LANGUAGE 'C';
--  CREATE TRUSTED PROCEDURAL LANGUAGE 'plpgsql' HANDLER plpgsql_call_handler LANCOMPILER 'PL/pgSQL';



CREATE TABLE lookup_code (
  source_table TEXT,
  source_field TEXT,
  lookup_seq INT2 DEFAULT 0,
  lookup_code TEXT,
  lookup_desc TEXT,
  lookup_misc TEXT
);
CREATE INDEX lookup_code_key ON lookup_code ( source_table, source_field, lookup_seq, lookup_code );
CREATE UNIQUE INDEX lookup_code_ak1 ON lookup_code ( source_table, source_field, lookup_code );
GRANT SELECT ON lookup_code TO PUBLIC;
GRANT SELECT,INSERT,UPDATE ON lookup_code TO general;



CREATE FUNCTION get_lookup_desc( TEXT, TEXT, TEXT )
    RETURNS TEXT
    AS 'SELECT lookup_desc AS RESULT FROM lookup_code 
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;'
    LANGUAGE 'sql';

CREATE FUNCTION get_lookup_misc( TEXT, TEXT, TEXT )
    RETURNS TEXT
    AS 'SELECT lookup_misc AS RESULT FROM lookup_code
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;'
    LANGUAGE 'sql';


CREATE FUNCTION get_status_desc(CHAR)
    RETURNS TEXT
    AS 'SELECT lookup_desc AS status_desc FROM lookup_code
            WHERE source_table=''request'' AND source_field=''status_code''
						AND lower(lookup_code) = lower($1)'
    LANGUAGE 'sql';




CREATE TABLE module (
   module_name TEXT PRIMARY KEY,
	 module_description TEXT,
	 module_seq INT4
);
GRANT SELECT ON module TO PUBLIC;
GRANT ALL ON module TO andrew;
CREATE INDEX xak1module ON module ( module_seq, module_name );


CREATE TABLE ugroup (
    group_no SERIAL,
		module_name TEXT,
    group_name TEXT );
GRANT SELECT ON ugroup TO PUBLIC;
GRANT ALL ON ugroup TO andrew;
CREATE FUNCTION max_group() RETURNS INT4 AS 'SELECT max(group_no) FROM ugroup' LANGUAGE 'sql';


CREATE TABLE group_member (
    group_no INT4,
    user_no INT4 );
GRANT SELECT,INSERT,UPDATE,DELETE ON group_member TO PUBLIC;
GRANT ALL ON group_member TO andrew;


CREATE TABLE session (
    session_id SERIAL,
    user_no INT4,
    session_start DATETIME DEFAULT TEXT 'now',
    session_end DATETIME DEFAULT TEXT 'now');
GRANT SELECT,INSERT,UPDATE ON session TO PUBLIC;
GRANT ALL ON session TO andrew;
CREATE FUNCTION max_session() RETURNS INT4 AS 'SELECT max(session_id) FROM session' LANGUAGE 'sql';

CREATE TABLE system_usr (
    user_no INT4,
		system_code TEXT,
		role CHAR,
		PRIMARY KEY ( user_no, system_code, role )
);
GRANT SELECT,INSERT,UPDATE ON system_usr TO PUBLIC;
GRANT ALL ON system_usr TO andrew;

CREATE TABLE org_usr (
    user_no INT4,
		org_code INT4,
		role CHAR,
		PRIMARY KEY ( user_no, org_code, role )
);
GRANT SELECT,INSERT,UPDATE ON org_usr TO PUBLIC;
GRANT ALL ON org_usr TO andrew;

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
      SELECT allocated_to INTO curr_val FROM request_allocated
			                WHERE allocated_to_id = u_no AND request_id = req_id;
      IF NOT FOUND THEN
        INSERT INTO request_allocated (allocated_to_id, request_id, allocated_to)
				    SELECT user_no, req_id, username FROM usr WHERE user_no = u_no;
      END IF;
      RETURN u_no;
   END;
' LANGUAGE 'plpgsql';
