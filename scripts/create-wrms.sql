CREATE TABLE usr (
  validated INT2 DEFAULT 0,
  enabled INT2 DEFAULT 1,
  access_level INT4 DEFAULT 10,
  last_accessed DATETIME,
  username TEXT NOT NULL UNIQUE PRIMARY KEY,
  email TEXT,
  fullname TEXT,
  password TEXT,
  note TEXT,
  org_code TEXT
) ;
GRANT SELECT,INSERT,UPDATE ON usr TO register;
GRANT SELECT,UPDATE ON usr TO general;
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
  org_code TEXT NOT NULL UNIQUE PRIMARY KEY,
  org_name TEXT,
  admin_usr TEXT
) ;
GRANT SELECT ON organisation TO PUBLIC;
GRANT INSERT,UPDATE,SELECT ON organisation TO general;

CREATE TABLE severity (
  severity_code INT2 NOT NULL UNIQUE PRIMARY KEY,
  severity_desc TEXT
) ;
GRANT SELECT ON severity TO PUBLIC;
GRANT INSERT,UPDATE,SELECT ON severity TO general;

CREATE TABLE status (
  status_code CHAR NOT NULL UNIQUE PRIMARY KEY,
  status_desc TEXT,
  next_responsibility_is TEXT
) ;
GRANT SELECT ON status TO PUBLIC;
GRANT INSERT,UPDATE,SELECT ON status TO general;

CREATE FUNCTION get_status_desc(CHAR)
    RETURNS TEXT
    AS 'SELECT status_desc FROM status
            WHERE lower(status_code) = lower($1)'
    LANGUAGE 'sql';

CREATE TABLE request_type (
  request_type INT2 NOT NULL UNIQUE PRIMARY KEY,
  request_type_desc TEXT
) ;
GRANT SELECT ON request_type TO PUBLIC;
GRANT INSERT,UPDATE,SELECT ON request_type TO general;

CREATE TABLE request (
  request_id SERIAL PRIMARY KEY,
  request_on DATETIME DEFAULT TEXT 'now',
  active BOOL DEFAULT TEXT 't',
  last_status CHAR DEFAULT 'N',
  severity_code INT2,
  request_type INT2,
  requester_id INT4,
  request_by TEXT,
  brief TEXT,
  detailed TEXT,
  system_code TEXT,
  eta DATETIME
) ;
CREATE INDEX xak0_request ON request ( active int4_ops, request_id );
CREATE INDEX xak1_request ON request ( active int4_ops, severity_code );
CREATE INDEX xak2_request ON request ( active int4_ops, severity_code, request_by );
CREATE INDEX xak3_request ON request ( active int4_ops, request_by );
CREATE INDEX xak4_request ON request ( active int4_ops, last_status );
GRANT INSERT,UPDATE,SELECT ON request TO general;
GRANT SELECT ON request_request_id_seq TO PUBLIC;

CREATE FUNCTION active_request(INT4)
    RETURNS BOOL
    AS 'SELECT active FROM request WHERE request.request_id = $1'
    LANGUAGE 'sql';
CREATE FUNCTION max_request()
    RETURNS INT4
    AS 'SELECT max(request_id) FROM request'
    LANGUAGE 'sql';
CREATE FUNCTION get_request_org(INT4)
    RETURNS TEXT
    AS 'SELECT usr.org_code FROM request, usr WHERE request.request_id = $1 AND request.request_by = usr.username'
    LANGUAGE 'sql';

CREATE TABLE work_system (
  system_code TEXT NOT NULL UNIQUE PRIMARY KEY,
  system_desc TEXT,
  notify_usr TEXT
) ;
GRANT SELECT ON work_system TO PUBLIC;
GRANT INSERT,UPDATE,SELECT ON work_system TO general;

CREATE TABLE org_system (
  org_code TEXT NOT NULL,
  system_code TEXT
) ;
GRANT SELECT ON org_system TO PUBLIC;
GRANT INSERT,UPDATE,SELECT ON work_system TO general;
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
GRANT SELECT ON request_quote_quote_id_seq TO PUBLIC;

CREATE FUNCTION max_quote() RETURNS INT4 AS 'SELECT max(quote_id) FROM request_quote' LANGUAGE 'sql';


CREATE TABLE request_allocated (
  request_id INT4,
  allocated_on DATETIME DEFAULT TEXT 'now',
  allocated_to TEXT
);
GRANT INSERT,UPDATE,SELECT ON request_allocated TO general;

CREATE TABLE request_timesheet (
  request_id INT4,
  work_on DATETIME,
  work_duration INTERVAL,
  work_by_id INT4,
  work_by TEXT,
  work_description TEXT,
  work_rate FLOAT8,
  work_charged DATETIME
);
GRANT INSERT,UPDATE,SELECT ON request_timesheet TO general;


CREATE TABLE request_note (
  request_id INT4,
  note_on DATETIME DEFAULT TEXT 'now',
  note_by_id INT4,
  note_by TEXT,
  note_detail TEXT
) ;
CREATE UNIQUE INDEX xpk_request_note ON request_note ( request_id, note_on );
GRANT INSERT,SELECT ON request_note TO PUBLIC;

CREATE FUNCTION get_last_note_on(INT4)
    RETURNS DATETIME
    AS 'SELECT max(note_on) FROM request_note WHERE request_note.request_id = $1'
    LANGUAGE 'sql';

CREATE TABLE request_interested (
  request_id INT4,
  username TEXT
) ;
CREATE UNIQUE INDEX xpk_request_interested ON request_interested ( request_id, username );
GRANT INSERT,SELECT,UPDATE,DELETE ON request_interested TO PUBLIC;

CREATE TABLE request_history (
  modified_on DATETIME DEFAULT TEXT 'now'
) INHERITS (request );
GRANT INSERT,SELECT ON request_history TO PUBLIC;
CREATE INDEX xpk_request_history ON request_history ( request_id, modified_on );

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
GRANT SELECT ON system_update_update_id_seq TO PUBLIC;

CREATE FUNCTION max_update() RETURNS INT4 AS 'SELECT max(update_id) FROM system_update' LANGUAGE 'sql';

CREATE TABLE request_update (
  request_id INT4,
  update_id INT4
);
CREATE INDEX xpk_request_update ON request_update ( request_id );
CREATE INDEX xak1_request_update ON request_update ( update_id );
GRANT INSERT,UPDATE,SELECT ON request_update TO general;

