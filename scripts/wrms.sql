CREATE TABLE organisation (
  org_code SERIAL PRIMARY KEY,
  active BOOLEAN DEFAULT TRUE,
  debtor_no INT4,
  work_rate FLOAT,
  abbreviation TEXT,
  current_sla BOOL,
  org_name TEXT
);
CREATE FUNCTION max_organisation() RETURNS INT4 AS 'SELECT max(org_code) FROM organisation' LANGUAGE 'sql';

CREATE TABLE organisation_tag (
   tag_id SERIAL PRIMARY KEY,
   org_code INT4 REFERENCES organisation,
   tag_description TEXT,
   tag_sequence INT4 DEFAULT 0,
   active BOOL DEFAULT TRUE
);
CREATE INDEX organisation_tag_sk1 ON organisation_tag( tag_sequence, lower(tag_description) );


-- Required actions for an organisation's requests
CREATE TABLE organisation_action (
   action_id SERIAL PRIMARY KEY,
   org_code INT4 REFERENCES organisation,
   action_description TEXT,
   action_sequence INT4 DEFAULT 0,
   active BOOL DEFAULT TRUE
);
CREATE INDEX organisation_action_sk1 ON organisation_action( org_code, action_sequence, lower(action_description) );


CREATE TABLE work_system (
  system_code TEXT NOT NULL UNIQUE PRIMARY KEY,
  system_desc TEXT,
  active BOOL
);

CREATE TABLE org_system (
  org_code INT4 NOT NULL REFERENCES organisation ( org_code ),
  system_code TEXT NOT NULL REFERENCES work_system ( system_code ),
  PRIMARY KEY ( org_code, system_code )
);
CREATE UNIQUE INDEX org_system_sk1 ON org_system ( system_code, org_code );


-- This is the table of users fir the system
CREATE TABLE usr (
  user_no SERIAL PRIMARY KEY,
  username TEXT NOT NULL UNIQUE,
  password TEXT,
  email TEXT,
  fullname TEXT,
  validated BOOLEAN DEFAULT FALSE,
  enabled BOOLEAN DEFAULT TRUE,
  last_accessed TIMESTAMP,
  joined TIMESTAMP DEFAULT current_timestamp,
  last_update TIMESTAMP,
  status CHAR,
  help BOOL,
  phone TEXT,
  mobile TEXT,
  pager TEXT,
  org_code INT4 REFERENCES organisation( org_code ),
  email_ok BOOLEAN DEFAULT TRUE,
  pager_ok BOOLEAN DEFAULT TRUE,
  phone_ok BOOLEAN DEFAULT TRUE,
  fax_ok   BOOLEAN DEFAULT TRUE,
  mail_style CHAR,
  config_data TEXT,
  note CHAR,
  location TEXT,
  base_rate NUMERIC
);
CREATE FUNCTION max_usr() RETURNS INT4 AS 'SELECT max(user_no) FROM usr' LANGUAGE 'sql';
CREATE UNIQUE INDEX usr_sk1 ON usr ( org_code, user_no );


CREATE TABLE system_usr (
  user_no INT4 REFERENCES usr ( user_no ),
  system_code TEXT REFERENCES work_system ( system_code ),
  role CHAR,
  PRIMARY KEY ( user_no, system_code )
);
CREATE UNIQUE INDEX system_usr_sk1 ON system_usr ( system_code, user_no );


CREATE TABLE ugroup (
  group_no SERIAL PRIMARY KEY,
  group_name TEXT
);
CREATE FUNCTION max_group() RETURNS INT4 AS 'SELECT max(group_no) FROM ugroup' LANGUAGE 'sql';


CREATE TABLE group_member (
  group_no INT4 REFERENCES ugroup ( group_no ),
  user_no INT4 REFERENCES usr ( user_no ),
  PRIMARY KEY ( group_no, user_no )
);
ALTER TABLE group_member ADD UNIQUE (user_no,group_no);
-- CREATE UNIQUE INDEX group_member_sk1 ON group_member ( user_no, group_no );


CREATE TABLE attachment_type (
   type_code TEXT PRIMARY KEY,
   type_desc TEXT,
   seq INT4,
   mime_type TEXT,
   pattern TEXT,
   mime_pattern TEXT
);


CREATE TABLE request (
  request_id SERIAL PRIMARY KEY,
  request_on TIMESTAMP DEFAULT current_timestamp,
  active BOOLEAN DEFAULT TRUE,
  last_status CHAR DEFAULT 'N',
  wap_status INT2 DEFAULT 0,
  sla_response_hours INT2 DEFAULT 0,
  urgency INT2,
  importance INT2,
  severity_code INT2,
  request_type INT2,
  requester_id INT4 REFERENCES usr ( user_no ),
  eta TIMESTAMP,
  last_activity TIMESTAMP DEFAULT current_timestamp,
  sla_response_time INTERVAL DEFAULT '0:00',
  sla_response_type CHAR DEFAULT 'O',
  requested_by_date TIMESTAMP,
  agreed_due_date TIMESTAMP,
  brief TEXT,
  detailed TEXT,
  system_code TEXT,
  entered_by INT4 REFERENCES usr ( user_no )
) ;
CREATE INDEX request_sk1 ON request ( requester_id ) WHERE active = TRUE;
CREATE INDEX request_sk2 ON request ( last_status ) WHERE active = TRUE;


CREATE TABLE request_status (
  request_id INT4 REFERENCES request ( request_id ),
  status_on TIMESTAMP,
  status_by_id INT4 REFERENCES usr ( user_no ),
  status_code TEXT,
  PRIMARY KEY (request_id, status_on )
);


CREATE TABLE request_note (
  request_id INT4 REFERENCES request ( request_id ),
  note_on TIMESTAMP DEFAULT current_timestamp,
  note_by_id INT4 REFERENCES usr ( user_no ),
  note_detail TEXT,
  PRIMARY KEY ( request_id, note_on )
);


CREATE TABLE request_quote (
  quote_id SERIAL PRIMARY KEY,
  request_id INT4 REFERENCES request ( request_id ),
  quoted_on TIMESTAMP DEFAULT current_timestamp,
  quote_amount FLOAT8,
  quote_by_id INT4 REFERENCES usr ( user_no ),
  quote_type TEXT,
  quote_units TEXT,
  quote_brief TEXT,
  quote_details TEXT,
  approved_by_id INT4 REFERENCES usr ( user_no ),
  approved_on TIMESTAMP,
  invoice_no INT4
);
CREATE INDEX request_quote_sk1 ON request_quote ( request_id, quoted_on );
CREATE FUNCTION max_quote() RETURNS INT4 AS 'SELECT max(quote_id) FROM request_quote' LANGUAGE 'sql';


CREATE TABLE request_allocated (
  request_id INT4 REFERENCES request ( request_id ),
  allocated_to_id INT4 REFERENCES usr ( user_no ),
  allocated_on TIMESTAMP DEFAULT current_timestamp,
  PRIMARY KEY ( request_id, allocated_to_id )
);
CREATE UNIQUE INDEX request_allocated_sk1 ON request_allocated ( allocated_to_id, request_id );


CREATE TABLE request_timesheet (
  timesheet_id SERIAL PRIMARY KEY,
  request_id INT4 REFERENCES request ( request_id ),
  work_on TIMESTAMP WITHOUT TIME ZONE,
  ok_to_charge BOOL DEFAULT FALSE,
  work_quantity FLOAT8,
  work_duration INTERVAL,
  work_by_id INT4 REFERENCES usr ( user_no ),
  work_description TEXT,
  work_rate FLOAT8,
  work_charged TIMESTAMP WITHOUT TIME ZONE,
  charged_amount FLOAT8,
  charged_by_id INT4 REFERENCES usr ( user_no ),
  work_units TEXT,
  charged_details TEXT,
  entry_details TEXT
);
CREATE INDEX request_timesheet_sk1 ON request_timesheet ( request_id, work_on );
CREATE INDEX request_timesheet_sk2 ON request_timesheet ( work_by_id, work_on );
CREATE FUNCTION max_timesheet() RETURNS INT4 AS 'SELECT max(timesheet_id) FROM request_timesheet' LANGUAGE 'sql';


CREATE TABLE request_interested (
  request_id INT4 REFERENCES request ( request_id ),
  user_no INT4 REFERENCES usr ( user_no ),
  username TEXT,
  PRIMARY KEY ( request_id, user_no )
) ;


---------------------------------------------------------------
-- Generic 'associated file' mechanism
---------------------------------------------------------------
CREATE TABLE request_attachment (
  attachment_id SERIAL PRIMARY KEY,
  request_id INT4 REFERENCES request ( request_id ),
  attached_on TIMESTAMP DEFAULT current_timestamp,
  attached_by INT4 REFERENCES usr ( user_no ),
  att_brief TEXT,
  att_description TEXT,
  att_filename TEXT,
  att_type TEXT REFERENCES attachment_type ( type_code ),
  att_inline BOOLEAN DEFAULT FALSE,
  att_width INT4,
  att_height INT4
) ;
CREATE INDEX request_attachment_skey ON request_attachment ( request_id );
CREATE FUNCTION max_attachment() RETURNS INT4 AS 'SELECT max(attachment_id) FROM request_attachment' LANGUAGE 'sql';


CREATE TABLE request_request (
  request_id INT4 REFERENCES request ( request_id ),
  to_request_id INT4 REFERENCES request ( request_id ),
  link_type CHAR,
  link_data TEXT,
  PRIMARY KEY ( request_id, link_type, to_request_id )
) ;
CREATE INDEX request_request_sk1 ON request_request ( to_request_id );


-- And the instances associated with each request
CREATE TABLE request_action (
   request_id INT4 REFERENCES request (request_id ),
   action_id INT4 REFERENCES organisation_action ( action_id ),
   completed_on TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp,
   updated_by_id INT4 REFERENCES usr( user_no ),
   PRIMARY KEY ( request_id, action_id )
);
CREATE INDEX request_action_sk1 ON request_action( action_id );


CREATE TABLE request_tag (
   request_id INT4 REFERENCES request,
   tag_id INT4 REFERENCES organisation_tag,
   tagged_on TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp,
   PRIMARY KEY ( request_id, tag_id )
);
CREATE INDEX request_tag_sk1 ON request_tag( tag_id );


CREATE TABLE timesheet_note (
  note_by_id INT4 REFERENCES usr ( user_no ),
  note_date TIMESTAMP,
  note_detail TEXT,
  PRIMARY KEY ( note_by_id, note_date )
);


CREATE TABLE request_history
  AS SELECT *, current_timestamp AS modified_on FROM request WHERE request_id != request_id;
ALTER TABLE request_history ALTER COLUMN modified_on SET DEFAULT current_timestamp;
CREATE UNIQUE INDEX request_history_sk1 ON request_history ( request_id, modified_on );





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


CREATE TABLE session (
    session_id SERIAL,
    user_no INT4 REFERENCES usr ( user_no ),
    help BOOL,
    session_start TIMESTAMP DEFAULT current_timestamp,
    session_end TIMESTAMP DEFAULT current_timestamp,
    session_config TEXT,
    session_key TEXT
);
CREATE FUNCTION max_session() RETURNS INT4 AS 'SELECT max(session_id) FROM session' LANGUAGE 'sql';


CREATE TABLE saved_queries (
    user_no INT4 REFERENCES usr ( user_no ),
    query_name TEXT,
    query_type TEXT,
    query_sql TEXT,
    query_params TEXT,
    maxresults INT,
    rlsort TEXT,
    rlseq TEXT,
    public BOOLEAN DEFAULT FALSE,
    updated TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp,
    in_menu BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (user_no, query_name)
);


CREATE TABLE help_hit (
    user_no INT4 REFERENCES usr ( user_no ),
    topic TEXT,
    times INT4,
    last TIMESTAMP,
    PRIMARY KEY (user_no, topic)
);

CREATE TABLE help (
    topic TEXT,
    seq INT4,
    title TEXT,
    content TEXT,
    PRIMARY KEY (topic, seq)
);

CREATE TABLE infonode (
    node_id SERIAL PRIMARY KEY,
    nodename TEXT,
    created_on TIMESTAMP DEFAULT current_timestamp,
    created_by INT4 REFERENCES usr ( user_no ),
    node_type INT4 DEFAULT 0
);
CREATE INDEX infonode_skey1 ON infonode (created_by, created_on);
CREATE INDEX infonode_skey2 ON infonode (created_on);

CREATE TABLE wu (
    node_id INT4,
    wu_by INT4 REFERENCES usr ( user_no ),
    wu_on TIMESTAMP DEFAULT current_timestamp,
    votes_plus INT4 DEFAULT 0,
    votes_minus INT4 DEFAULT 0,
    flags TEXT DEFAULT '',
    content TEXT,
    PRIMARY KEY (node_id, wu_by)
);
CREATE INDEX wu_skey1 ON wu (wu_by, wu_on);
CREATE INDEX wu_skey2 ON wu (wu_on);

CREATE TABLE wu_vote (
    node_id INT4,
    wu_by INT4 REFERENCES usr ( user_no ),
    vote_by INT4 REFERENCES usr ( user_no ),
    vote_amount INT4,
    flag CHAR,
    vote_on TIMESTAMP DEFAULT current_timestamp,
    PRIMARY KEY ( node_id, wu_by, vote_by )
);

CREATE TABLE nodetrack (
    node_from INT4,
    node_to INT4,
    no_times INT4,
    PRIMARY KEY (node_from, node_to)
);
CREATE INDEX nodetrack_skey1 ON nodetrack(node_from, node_to);


CREATE TABLE wrms_revision (
   schema_id INT4,
   schema_major INT4,
   schema_minor INT4,
   schema_patch INT4,
   schema_name TEXT,
   applied_on TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp
);
GRANT SELECT ON wrms_revision TO general;


GRANT INSERT, UPDATE, SELECT, DELETE ON
  request_tag, organisation_tag, organisation_tag_tag_id_seq
  TO general;


GRANT SELECT ON ugroup TO general;

GRANT INSERT, UPDATE, SELECT ON
  request, request_request_id_seq,
  request_quote, request_quote_quote_id_seq,
  request_status, request_note,
  request_request, request_history,
  request_attachment,
  lookup_code,
  attachment_type,
  session, session_session_id_seq,
  work_system,
  usr, usr_user_no_seq,
  organisation, organisation_org_code_seq,
  help, help_hit,
  infonode, infonode_node_id_seq, wu, wu_vote, nodetrack
  TO general;


GRANT INSERT,UPDATE,SELECT, DELETE ON
  request_timesheet,
  request_allocated, request_interested,
  org_system, request_request,
  timesheet_note,
  group_member,
  request_tag, organisation_tag, organisation_tag_tag_id_seq,
  system_usr,
  saved_queries,
  request_action, organisation_action, organisation_action_action_id_seq,
  attachment_type
  TO general;


-- One of these sets will fail for 7.2, one for 7.3
-- 7.2 and earlier
-- GRANT INSERT, UPDATE, SELECT ON request_attac_attachment_id_seq TO general;
-- GRANT INSERT,UPDATE,SELECT ON request_timesh_timesheet_id_seq TO general;
-- 7.3 and later...
GRANT INSERT, UPDATE, SELECT ON request_attachment_attachment_id_seq TO general;
GRANT INSERT,UPDATE,SELECT ON request_timesheet_timesheet_id_seq TO general;

\i procedures.sql

SELECT new_wrms_revision(2,0,1, 'Baguette' );

ALTER TABLE group_member CLUSTER ON group_member_user_no_key;
ALTER TABLE system_usr CLUSTER ON system_usr_pkey;
ALTER TABLE request_interested CLUSTER ON request_interested_pkey;
ALTER TABLE request_note CLUSTER ON request_note_pkey;
ALTER TABLE request_status CLUSTER ON request_status_pkey;
ALTER TABLE request_allocated CLUSTER ON request_allocated_pkey;
ALTER TABLE request_attachment CLUSTER ON request_attachment_pkey;
ALTER TABLE request_quote CLUSTER ON request_quote_pkey;
ALTER TABLE request_action CLUSTER ON request_action_pkey;
ALTER TABLE request_tag CLUSTER ON request_tag_pkey;
ALTER TABLE request_request CLUSTER ON request_request_sk1;
ALTER TABLE request_timesheet CLUSTER ON request_timesheet_sk1;

CLUSTER group_member;
CLUSTER system_usr;
CLUSTER request_interested;
CLUSTER request_note;
CLUSTER request_status;
CLUSTER request_allocated;
CLUSTER request_attachment;
CLUSTER request_quote;
CLUSTER request_action;
CLUSTER request_tag;
CLUSTER request_request;
CLUSTER request_timesheet;
