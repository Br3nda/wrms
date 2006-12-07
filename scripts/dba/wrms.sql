
-- awl differs
--CREATE FUNCTION get_usr_setting(TEXT,TEXT)
--    RETURNS TEXT
--    AS 'SELECT setting_value FROM usr_setting
--            WHERE usr_setting.username = $1
--            AND usr_setting.setting_name = $2 ' LANGUAGE 'sql';


CREATE TABLE organisation (
  org_code SERIAL PRIMARY KEY,
  active BOOL DEFAULT TRUE,
  debtor_no INT4,
  work_rate FLOAT,
  admin_user_no INT4,
  support_user_no INT4,
  abbreviation TEXT,
  current_sla BOOL,
  org_name TEXT,
  admin_usr TEXT,
  general_system INT
) ;
CREATE FUNCTION max_organisation() RETURNS INT4 AS 'SELECT max(org_code) FROM organisation' LANGUAGE 'sql';

-- alter the usr table to add the neccassary columns
ALTER TABLE usr ADD org_code INT4 REFERENCES organisation( org_code );
ALTER TABLE usr ADD last_update TIMESTAMPTZ;
ALTER TABLE usr ADD location TEXT;
ALTER TABLE usr ADD mobile TEXT;
ALTER TABLE usr ADD phone TEXT;
ALTER TABLE usr DROP email_ok;
ALTER TABLE usr ADD email_ok BOOL;

CREATE TABLE request (
  request_id SERIAL PRIMARY KEY,
  request_on TIMESTAMP DEFAULT current_timestamp,
  active BOOL DEFAULT TRUE,
  last_status CHAR DEFAULT 'N',
  wap_status INT2 DEFAULT 0,
  sla_response_hours INT2 DEFAULT 0,
  urgency INT2,
  importance INT2,
  severity_code INT2,
  request_type INT2,
  requester_id INT4,
  eta TIMESTAMP,
  last_activity TIMESTAMP DEFAULT current_timestamp,
  sla_response_time INTERVAL DEFAULT '0:00',
  sla_response_type CHAR DEFAULT 'O',
  requested_by_date TIMESTAMP,
  agreed_due_date TIMESTAMP,
  request_by TEXT,
  brief TEXT,
  detailed TEXT,
  system_code TEXT,
  entered_by INT4
) ;
CREATE INDEX xak0_request ON request ( active, request_id );
CREATE INDEX xak1_request ON request ( active, severity_code );
CREATE INDEX xak2_request ON request ( active, severity_code, request_by );
CREATE INDEX xak3_request ON request ( active, request_by );
CREATE INDEX xak4_request ON request ( active, last_status );

CREATE TABLE work_system (
  system_id INT,
  organisation_specific BOOL,
  system_code TEXT NOT NULL UNIQUE PRIMARY KEY,
  system_desc TEXT,
  active BOOL,
  support_user_no INT4,
  notify_usr TEXT
) ;

CREATE TABLE org_system (
  org_code INT4 NOT NULL,
  admin_user_no INT4,
  support_user_no INT4,
  system_code TEXT,
  system_id INT
) ;
CREATE INDEX xpk_org_system ON org_system ( org_code, system_code );
CREATE INDEX xak1_org_system ON org_system ( system_code, org_code );

CREATE TABLE request_status (
  request_id INT4,
  status_on TIMESTAMP,
  status_by_id INT4,
  status_by TEXT,
  status_code TEXT
) ;
CREATE UNIQUE INDEX xpk_request_status ON request_status ( request_id, status_on );

CREATE TABLE request_quote (
  quote_id SERIAL PRIMARY KEY,
  request_id INT4,
  quoted_on TIMESTAMP DEFAULT current_timestamp,
  quote_amount FLOAT8,
  quote_by_id INT4,
  quoted_by TEXT,
  quote_type TEXT,
  quote_units TEXT,
  quote_brief TEXT,
  quote_details TEXT,
  approved_by_id INT4,
  approved_on TIMESTAMP,
  invoice_no INT4
);
CREATE INDEX xak1_request_quote ON request_quote ( request_id );

CREATE FUNCTION max_quote() RETURNS INT4 AS 'SELECT max(quote_id) FROM request_quote' LANGUAGE 'sql';


CREATE TABLE request_allocated (
  request_id INT4,
  allocated_on TIMESTAMP DEFAULT current_timestamp,
  allocated_to_id INT4,
  allocated_to TEXT
);

CREATE TABLE request_timesheet (
  timesheet_id SERIAL PRIMARY KEY,
  request_id INT4,
  work_on TIMESTAMP WITHOUT TIME ZONE,
  ok_to_charge BOOL,
  work_quantity FLOAT8,
  work_duration INTERVAL,
  work_by_id INT4,
  work_by TEXT,
  work_description TEXT,
  work_rate FLOAT8,
  work_charged TIMESTAMP WITHOUT TIME ZONE,
  charged_amount FLOAT8,
  charged_by_id INT4,
  work_units TEXT,
  charged_details TEXT,
  entry_details TEXT
);
CREATE INDEX request_timesheet_skey1 ON request_timesheet ( work_on, work_by_id, request_id );
CREATE INDEX request_timesheet_skey2 ON request_timesheet ( ok_to_charge, request_id );
CREATE FUNCTION max_timesheet() RETURNS INT4 AS 'SELECT max(timesheet_id) FROM request_timesheet' LANGUAGE 'sql';

CREATE TABLE timesheet_note (
  note_date TIMESTAMP,
  note_by_id INT4,
  note_detail TEXT,
  PRIMARY KEY ( note_date, note_by_id )
) ;

CREATE TABLE request_note (
  request_id INT4,
  note_on TIMESTAMP DEFAULT current_timestamp,
  note_by_id INT4,
  note_by TEXT,
  note_detail TEXT,
  PRIMARY KEY ( request_id, note_on )
) ;

CREATE FUNCTION get_last_note_on(INT4)
    RETURNS TIMESTAMP
    AS 'SELECT max(note_on) FROM request_note WHERE request_note.request_id = $1
    ' LANGUAGE 'sql';

CREATE TABLE request_interested (
  request_id INT4,
  user_no INT4 DEFAULT -1,
  username TEXT,
  PRIMARY KEY ( request_id, username )
) ;

CREATE TABLE request_request (
  request_id INT4,
  to_request_id INT4,
  link_type CHAR,
  link_data TEXT,
  PRIMARY KEY ( request_id, link_type, to_request_id )
) ;
CREATE INDEX request_request_sk1 ON request_request ( to_request_id );


CREATE TABLE request_history
  AS SELECT *, current_timestamp AS modified_on FROM request WHERE request_id != request_id;
ALTER TABLE request_history ALTER COLUMN modified_on SET DEFAULT current_timestamp;
CREATE INDEX xpk_request_history ON request_history ( request_id, modified_on );


---------------------------------------------------------------
-- Generic 'associated file' mechanism
---------------------------------------------------------------
CREATE TABLE request_attachment (
  attachment_id SERIAL PRIMARY KEY,
  request_id INT4,
  attached_on TIMESTAMP DEFAULT current_timestamp,
  attached_by INT4,
  att_brief TEXT,
  att_description TEXT,
  att_filename TEXT,
  att_type TEXT,
  att_inline BOOLEAN DEFAULT FALSE,
  att_width INT4,
  att_height INT4
) ;
CREATE INDEX request_attachment_skey ON request_attachment ( request_id );

CREATE FUNCTION max_attachment() RETURNS INT4 AS 'SELECT max(attachment_id) FROM request_attachment' LANGUAGE 'sql';




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



CREATE FUNCTION get_lookup_desc( TEXT, TEXT, TEXT )
    RETURNS TEXT
    AS 'SELECT lookup_desc AS RESULT FROM lookup_code
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;' LANGUAGE 'sql';

CREATE FUNCTION get_lookup_misc( TEXT, TEXT, TEXT )
    RETURNS TEXT
    AS 'SELECT lookup_misc AS RESULT FROM lookup_code
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;' LANGUAGE 'sql';


CREATE FUNCTION get_status_desc(CHAR)
    RETURNS TEXT
    AS 'SELECT lookup_desc AS status_desc FROM lookup_code
            WHERE source_table=''request'' AND source_field=''status_code''
            AND lower(lookup_code) = lower($1)
    ' LANGUAGE 'sql';



CREATE TABLE attachment_type (
   type_code TEXT PRIMARY KEY,
   type_desc TEXT,
   seq INT4,
   mime_type TEXT,
   pattern TEXT,
   mime_pattern TEXT
);

CREATE TABLE module (
   module_name TEXT PRIMARY KEY,
   module_description TEXT,
   module_seq INT4
);
CREATE INDEX xak1module ON module ( module_seq, module_name );


CREATE TABLE ugroup (
    group_no SERIAL,
    module_name TEXT,
    group_name TEXT );
CREATE FUNCTION max_group() RETURNS INT4 AS 'SELECT max(group_no) FROM ugroup' LANGUAGE 'sql';


CREATE TABLE group_member (
    group_no INT4,
    user_no INT4 );


CREATE TABLE system_usr (
    user_no INT4,
    system_code TEXT,
    system_id INT,
    role CHAR,
    PRIMARY KEY ( user_no, system_code, role )
);

CREATE TABLE saved_queries (
    user_no INT4,
    query_name TEXT,
    query_type TEXT,
    query_sql TEXT,
    query_params TEXT,
    maxresults INT,
    rlsort TEXT,
    rlseq TEXT,
    PRIMARY KEY (user_no, query_name)
);

CREATE TABLE help_hit (
    user_no INT4,
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
    created_by INT4,
    node_type INT4 DEFAULT 0
);
CREATE INDEX infonode_skey1 ON infonode (created_by, created_on);
CREATE INDEX infonode_skey2 ON infonode (created_on);

CREATE TABLE wu (
    node_id INT4,
    wu_by INT4,
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
    wu_by INT4,
    vote_by INT4,
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

GRANT SELECT ON module, ugroup TO general;

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
  usr, usr_user_no_seq, usr_setting,
  organisation, organisation_org_code_seq,
  help, help_hit,
  infonode, infonode_node_id_seq, wu, wu_vote, nodetrack
  TO general;

-- One of these will fail for 7.2, one for 7.3
GRANT INSERT, UPDATE, SELECT ON request_attachment_attachment_id_seq TO general;
GRANT INSERT, UPDATE, SELECT ON request_attac_attachment_id_seq TO general;

CREATE TABLE organisation_tag (
   tag_id SERIAL PRIMARY KEY,
   org_code INT4 REFERENCES organisation,
   tag_description TEXT,
   tag_sequence INT4 DEFAULT 0,
   active BOOL DEFAULT TRUE
);

CREATE INDEX organisation_tag_sk1 ON organisation_tag( tag_sequence, lower(tag_description) );

CREATE TABLE request_tag (
   request_id INT4 REFERENCES request,
   tag_id INT4 REFERENCES organisation_tag,
   tagged_on TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp,
   PRIMARY KEY ( request_id, tag_id )
);
CREATE INDEX request_tag_sk1 ON request_tag( tag_id );

GRANT INSERT,UPDATE,SELECT, DELETE ON
  request_timesheet,
  request_allocated, request_interested,
  org_system,
  timesheet_note,
  group_member,
  request_tag, organisation_tag, organisation_tag_tag_id_seq,
  system_usr,
  saved_queries
  TO general;

  -- Backward compatibility with 7.2...
-- GRANT INSERT,UPDATE,SELECT, DELETE ON request_timesh_timesheet_id_seq TO general;
  -- Forward compatibility with 7.2...
GRANT INSERT,UPDATE,SELECT, DELETE ON request_timesheet_timesheet_id_seq TO general;

CREATE TABLE wrms_revision (
 schema_id  INT4,
 schema_major INT4,
 schema_minor INT4,
 schema_patch INT4,
 schema_name TEXT,
 applied_on  TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp
 );
