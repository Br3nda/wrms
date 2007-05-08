
CREATE TABLE work_system (
  system_id SERIAL PRIMARY KEY,
  organisation_specific BOOL,
  system_code TEXT NOT NULL UNIQUE,
  system_desc TEXT,
  active BOOL
);


CREATE TABLE organisation (
  org_code SERIAL PRIMARY KEY,
  active BOOL DEFAULT TRUE,
  debtor_no INT4,
  work_rate FLOAT,
  admin_user_no INT4,    -- deprecated
  support_user_no INT4,  -- deprecated
  abbreviation TEXT,
  current_sla BOOL,
  org_name TEXT,
  general_system INT REFERENCES work_system(system_id)
) ;

CREATE TABLE org_system (
  org_code INT4 REFERENCES organisation(org_code),
  system_id INT  REFERENCES work_system(system_id),
  PRIMARY KEY ( org_code, system_id )
) ;
CREATE INDEX xak1_org_system ON org_system ( system_id, org_code );


-- alter the usr table to add the neccassary columns
ALTER TABLE usr ADD COLUMN org_code INT4 REFERENCES organisation( org_code );
ALTER TABLE usr ADD COLUMN last_update TIMESTAMPTZ;
ALTER TABLE usr ADD COLUMN location TEXT;
ALTER TABLE usr ADD COLUMN mobile TEXT;
ALTER TABLE usr ADD COLUMN phone TEXT;

-- This can probably be resolved better by changing existing DBs to use a timestamp
-- ALTER TABLE usr DROP email_ok;
-- ALTER TABLE usr ADD email_ok BOOL;

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
  requester_id INT4 REFERENCES usr(user_no),
  eta TIMESTAMP,
  last_activity TIMESTAMP DEFAULT current_timestamp,
  sla_response_time INTERVAL DEFAULT '0:00',
  sla_response_type CHAR DEFAULT 'O',
  requested_by_date TIMESTAMP,
  agreed_due_date TIMESTAMP,
  request_by TEXT,
  brief TEXT,
  detailed TEXT,
  system_id	INT4 NOT NULL REFERENCES work_system(system_id),
  entered_by INT4 REFERENCES usr(user_no),
  parent_request INT4 REFERENCES request(request_id)
) ;
CREATE INDEX xak0_request ON request ( active, request_id );
CREATE INDEX xak1_request ON request ( active, severity_code );
CREATE INDEX xak2_request ON request ( active, severity_code, request_by );
CREATE INDEX xak3_request ON request ( active, request_by );
CREATE INDEX xak4_request ON request ( active, last_status );

CREATE TABLE request_status (
  request_id INT4 REFERENCES request(request_id),
  status_on TIMESTAMP,
  status_by_id INT4 REFERENCES usr(user_no),
  status_by TEXT,
  status_code TEXT
) ;
CREATE INDEX xpk_request_status ON request_status ( request_id, status_on );

CREATE TABLE request_quote (
  quote_id SERIAL PRIMARY KEY,
  request_id INT4 REFERENCES request(request_id),
  quoted_on TIMESTAMP DEFAULT current_timestamp,
  quote_amount FLOAT8,
  quote_by_id INT4 REFERENCES usr(user_no),
  quoted_by TEXT,
  quote_type TEXT,
  quote_units TEXT,
  quote_brief TEXT,
  quote_details TEXT,
  approved_by_id INT4 REFERENCES usr(user_no),
  approved_on TIMESTAMP,
  invoice_no INT4
);
CREATE INDEX request_quote_sk1 ON request_quote ( request_id );


CREATE TABLE request_allocated (
  request_id INT4 REFERENCES request(request_id),
  allocated_on TIMESTAMP DEFAULT current_timestamp,
  allocated_to_id INT4 REFERENCES usr(user_no),
  allocated_to TEXT
);

CREATE TABLE request_timesheet (
  timesheet_id SERIAL PRIMARY KEY,
  request_id INT4 REFERENCES request(request_id),
  work_on TIMESTAMP WITHOUT TIME ZONE,
  ok_to_charge BOOL,
  work_quantity FLOAT8,
  work_duration INTERVAL,
  work_by_id INT4 REFERENCES usr(user_no),
  work_by TEXT,
  work_description TEXT,
  work_rate FLOAT8,
  work_charged TIMESTAMP WITHOUT TIME ZONE,
  charged_amount FLOAT8,
  charged_by_id INT4 REFERENCES usr(user_no),
  work_units TEXT,
  charged_details TEXT,
  entry_details TEXT,
  dav_etag TEXT
);
CREATE INDEX request_timesheet_skey1 ON request_timesheet ( work_on, work_by_id, request_id );
CREATE INDEX request_timesheet_skey2 ON request_timesheet ( ok_to_charge, request_id );
CREATE INDEX request_timesheet_sk3 ON request_timesheet ( request_id, timesheet_id );
CREATE UNIQUE INDEX request_timesheet_sk4 ON request_timesheet ( work_by_id, dav_etag );

CREATE TABLE timesheet_note (
  note_date TIMESTAMP,
  note_by_id INT4 REFERENCES usr(user_no),
  note_detail TEXT,
  PRIMARY KEY ( note_by_id, note_date )
) ;

CREATE TABLE request_note (
  request_id INT4 REFERENCES request(request_id),
  note_on TIMESTAMP DEFAULT current_timestamp,
  note_by_id INT4 REFERENCES usr(user_no),
  note_by TEXT,
  note_detail TEXT,
  PRIMARY KEY ( request_id, note_on )
);

CREATE TABLE request_qa_action (
  request_id INT4 REFERENCES request ( request_id ),
  action_on TIMESTAMP DEFAULT current_timestamp,
  action_by INT4 REFERENCES usr ( user_no ),
  action_detail TEXT,
  PRIMARY KEY ( request_id, action_on )
);

CREATE TABLE request_interested (
  request_id INT4 REFERENCES request(request_id),
  user_no INT4 REFERENCES usr(user_no),
  username TEXT,
  PRIMARY KEY ( request_id, user_no )
) ;

CREATE TABLE request_request (
  request_id INT4 REFERENCES request(request_id),
  to_request_id INT4 REFERENCES request(request_id),
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
  request_id INT4 REFERENCES request(request_id),
  attached_on TIMESTAMP DEFAULT current_timestamp,
  attached_by INT4 REFERENCES usr(user_no),
  att_brief TEXT,
  att_description TEXT,
  att_filename TEXT,
  att_type TEXT,
  att_inline BOOLEAN DEFAULT FALSE,
  att_width INT4,
  att_height INT4
) ;
CREATE INDEX request_attachment_skey ON request_attachment ( request_id );


-- *********************************


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



CREATE TABLE attachment_type (
   type_code TEXT PRIMARY KEY,
   type_desc TEXT,
   seq INT4,
   mime_type TEXT,
   pattern TEXT,
   mime_pattern TEXT
);

-- Deprecated.  Nothing should use this, but need to check.
--CREATE TABLE module (
--   module_name TEXT PRIMARY KEY,
--   module_description TEXT,
--   module_seq INT4
--);
--CREATE INDEX xak1module ON module ( module_seq, module_name );


CREATE TABLE system_usr (
    user_no INT4 REFERENCES usr(user_no),
    system_id INT REFERENCES work_system(system_id),
    role CHAR,
    PRIMARY KEY ( user_no, system_id )
);
CREATE INDEX system_usr_sk1 ON system_usr( system_id, user_no );

CREATE TABLE saved_queries (
    user_no INT4 REFERENCES usr(user_no),
    query_name TEXT,
    query_type TEXT,
    query_sql TEXT,
    query_params TEXT,
    maxresults INT,
    rlsort TEXT,
    rlseq TEXT,
    public	boolean default false,
    updated	timestamp with time zone DEFAULT current_timestamp,
    in_menu	boolean default false,
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


CREATE TABLE organisation_tag (
   tag_id SERIAL PRIMARY KEY,
   org_code INT4 REFERENCES organisation,
   tag_description TEXT,
   tag_sequence INT4 DEFAULT 0,
   active BOOL DEFAULT TRUE
);

CREATE INDEX organisation_tag_sk1 ON organisation_tag( org_code, tag_sequence, lower(tag_description) );

CREATE TABLE request_tag (
   request_id INT4 REFERENCES request,
   tag_id INT4 REFERENCES organisation_tag,
   tagged_on TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp,
   PRIMARY KEY ( request_id, tag_id )
);
CREATE INDEX request_tag_sk1 ON request_tag( tag_id );

CREATE TABLE organisation_action (
  action_id SERIAL,
  org_code  INT4 REFERENCES organisation(org_code),
  action_description  TEXT,
  action_sequence INT4 DEFAULT 0,
  active  BOOL DEFAULT TRUE
);



CREATE TABLE qa_document (
qa_document_id       SERIAL               not null PRIMARY KEY,
qa_document_title    TEXT                 not null,
qa_document_desc     TEXT                 null
);


CREATE TABLE qa_phase (
qa_phase             TEXT                 not null PRIMARY KEY,
qa_phase_desc        TEXT                 null,
qa_phase_order       INT4                 not null default 0
);

comment on table qa_phase is
'Contains all of the Quality Assurance Phases available. A QA Phase is a logical grouping of QA Steps. Useful for display and reporting purposes.';


CREATE TABLE qa_step (
qa_step_id           SERIAL               primary key not null,
qa_phase             TEXT                 not null REFERENCES qa_phase(qa_phase),
qa_document_id       INT4                 null REFERENCES qa_document(qa_document_id),
qa_step_desc         TEXT                 null,
qa_step_notes        TEXT                 null,
qa_step_order        INT4                 not null default 0,
mandatory            BOOL                 not null default false,
enabled              BOOL                 not null default true
);


comment on table qa_step is
'Contains all of the Quality Assurance Steps that are allowed in a project. A QA Step is a task which needs to be achieved as part of the QA process, and must be QA approved.';


CREATE TABLE qa_approval_type (
qa_approval_type_id  SERIAL                not null PRIMARY KEY,
qa_approval_type_desc TEXT                 not null
);

comment on table qa_approval_type is
'Contains Quality Assurance Approval Types. A QA Approval Type represents a particular kind of approval required for a QA Step. Examples would be ''Internal Approval'', ''Peer Review'', ''Maintainer Approval'', or ''Client Approval''.';


CREATE TABLE qa_approval (
qa_step_id           SERIAL               not null REFERENCES qa_step(qa_step_id),
qa_approval_type_id  INT4                 not null REFERENCES qa_approval_type(qa_approval_type_id),
qa_approval_order    INT4                 not null default 0,
constraint PK_QA_APPROVAL primary key (qa_step_id, qa_approval_type_id)
);

comment on table qa_approval is
'Contains the required Quality Assurance Approvals for given QA Step. A QA Approval is associated with a given QA Step. The contents of this table define which approvals records have to be created for QA Steps, when you create the QA instance records for a project.';


CREATE TABLE qa_model (
qa_model_id          SERIAL               not null primary key,
qa_model_name        TEXT                 not null,
qa_model_desc        TEXT                 null,
qa_model_order       INT4                 not null default 0
);

comment on table qa_model is
'Contains Quality Assurance models. A model is simply a hypothetical QA profile which defines the QA requirements
for that profile. It provides a kind of template for assigning default QA Steps etc. There are three simple models
which have been invented to begin with: Small, Medium and Large (referring to project size).';


CREATE TABLE qa_model_documents (
qa_model_id          INT4                 not null REFERENCES qa_model(qa_model_id),
qa_document_id       INT4                 not null REFERENCES qa_document(qa_document_id),
path_to_template     TEXT                 null,
path_to_example      TEXT                 null,
constraint PK_QA_MODEL_DOCUMENTS primary key (qa_model_id, qa_document_id)
);


CREATE TABLE qa_model_step (
qa_model_id          INT4                 not null REFERENCES qa_model(qa_model_id),
qa_step_id           INT4                 not null REFERENCES qa_step(qa_step_id),
constraint PK_QA_MODEL_STEP primary key (qa_model_id, qa_step_id)
);



CREATE TABLE request_project (
request_id           INT4                 PRIMARY KEY not null REFERENCES request(request_id),
project_manager      INT4                 null REFERENCES usr (user_no),
qa_mentor            INT4                 null REFERENCES usr (user_no),
qa_model_id          INT4                 null REFERENCES qa_model (qa_model_id),
qa_phase             TEXT                 null REFERENCES qa_phase (qa_phase)
);

comment on table request_project is
'Contains the master records for projects. Every project has a master WRMS record associated with it, and this table contains pointers to those.';

comment on column request_project.project_manager is
'The user who is the designated project manager for this project.';

comment on column request_project.qa_mentor is
'The user who is designated as the person to help out and guide the project team in quality assurance matters.';

comment on column request_project.qa_model_id is
'The initial choice by the project creator, of the model that the project is closest to in size.';

comment on column request_project.qa_phase is
'The current phase that the project is in. Updated whenever approval action takes place. Can be used as a means of high-level project progress viewing.';



CREATE TABLE qa_project_step (
project_id           INT4                 not null REFERENCES request_project (request_id),
qa_step_id           INT4                 not null REFERENCES qa_step(qa_step_id),
request_id           INT4                 not null REFERENCES request(request_id),
responsible_usr      INT4                 null REFERENCES usr (user_no),
responsible_datetime TIMESTAMP            null,
notes                TEXT                 null,
constraint PK_QA_PROJECT_STEP primary key (project_id, qa_step_id)
);


comment on table qa_project_step is
'The Project QA Step table contains the QA Steps defined for a given project. Each step is associated with a WRMS request record, which can be used to attach QA documents etc. and also for final signoff of the task once all the required approvals have been acquired.';

comment on column qa_project_step.project_id is
'The unique ID for a project. This is actually a WRMS request ID of the master WRMS record  created for this project.';

comment on column qa_project_step.qa_step_id is
'This is the QA Step being processed for the given project.';

comment on column qa_project_step.request_id is
'This is the foreign key to the WRMS record for this QA Step. This WRMS record is used during the processing of this QA Step, for attaching docs, making notes etc. in the usual WRMS fashion.';

comment on column qa_project_step.responsible_usr is
'This user is assigned to the QA Step as the person responsible for delivering it and getting it approved. The person is selected from those allocated to the project.';

comment on column qa_project_step.responsible_datetime is
'The datetime that the user responsible for this step was assigned to it.';



CREATE TABLE qa_project_approval (
qa_approval_id       SERIAL               not null PRIMARY KEY,
project_id           INT4                 not null REFERENCES request_project (request_id),
qa_step_id           INT4                 not null REFERENCES qa_step(qa_step_id),
qa_approval_type_id  INT4                 not null REFERENCES qa_approval_type(qa_approval_type_id),
approval_status      TEXT                 null
      constraint CKC_APPROVAL_STATUS_QA_PROJE check (approval_status is null or ( approval_status in ('p','y','n','s') )),
assigned_to_usr      INT4                 null REFERENCES usr (user_no),
assigned_datetime    TIMESTAMP            null,
approval_by_usr      INT4                 null REFERENCES usr (user_no),
approval_datetime    TIMESTAMP            null,
comment              TEXT                 null,
constraint FK_PROJECT_QA_APPROVAL_STEP FOREIGN KEY (project_id, qa_step_id)
      REFERENCES qa_project_step (project_id, qa_step_id)
);

comment on table qa_project_approval is
'Contains Quality Assurance Approvals. A QA Approval record is associated with a given project QA Step and is only created when someone who is permitted to do so seeks to acquire an approval for the given QA Step. NB: QA Approval records are ''read-only'' to the QA application - they are an audit trail of approval activities. A QA user can create as many approvals for the same project, QA Step and Approval Type as they wish - they just keep adding to the approval audit trail.';

comment on column qa_project_approval.assigned_to_usr is
'The user that the approval process is assigned to - by the Project Manager. The approval is then approved by someone allocated to the project, or the project manager or the QA mentor (all are permitted to do it), however if the assigned user does not match the approved-by user, the approval has been explicitly over-ridden.';

comment on column qa_project_approval.approval_by_usr is
'The user who is updating this approval status.';

comment on column qa_project_approval.approval_datetime is
'Time and date that the approval status was changed to the current status.';

comment on column qa_project_approval.comment is
'Used to make brief comments on this approval.';



CREATE TABLE qa_project_step_approval (
project_id           INT4                 not null REFERENCES request_project (request_id),
qa_step_id           INT4                 not null REFERENCES qa_step(qa_step_id),
qa_approval_type_id  INT4                 not null REFERENCES qa_approval_type (qa_approval_type_id),
last_approval_status TEXT                 null
      constraint CKC_LAST_APPROVAL_STA_QA_PROJE check (last_approval_status is null or ( last_approval_status in ('p','y','n','s') )),
constraint PK_QA_PROJECT_STEP_APPROVAL primary key (project_id, qa_step_id, qa_approval_type_id),
constraint FK_PROJ_STEP_APPROVAL FOREIGN KEY (project_id, qa_step_id) REFERENCES qa_project_step (project_id, qa_step_id)
);


comment on table qa_project_step_approval is
'This contains the list of approval types which are required for a given project QA step. It starts off as the default types as expressed by the ''qa_approval'' table, but may be subsequently modified by the project manager to add or subtract approval types. The presence of one of these records indicates that the given approval type is required for the project QA step. Note that this record also holds a denormalised value of the last approval status registered for this type.';

comment on column qa_project_step_approval.project_id is
'The unique ID for a project. This is actually a WRMS request ID of the master WRMS record  created for this project.';

comment on column qa_project_step_approval.qa_step_id is
'This is the QA Step being processed for the given project.';



-- Alterations to the tables added by the AWL initialisation

ALTER TABLE roles ADD seq integer;
ALTER TABLE roles ADD module_name text;

ALTER TABLE usr ADD validated smallint;
 ALTER TABLE usr ALTER validated SET DEFAULT 0;

ALTER TABLE usr ADD enabled	smallint;
 ALTER TABLE usr ALTER enabled SET DEFAULT 1;

ALTER TABLE usr ADD access_level INT4;
 ALTER TABLE usr ALTER access_level SET default 10;

ALTER TABLE usr ADD linked_user	INT4;
ALTER TABLE usr ADD help	boolean;
ALTER TABLE usr ADD pager	text;

ALTER TABLE usr ADD pager_ok boolean;
 ALTER TABLE usr ALTER pager_ok SET default true;

ALTER TABLE usr ADD phone_ok	boolean;
 ALTER TABLE usr ALTER phone_ok SET default true;

ALTER TABLE usr ADD fax_ok	boolean;
 ALTER TABLE usr ALTER fax_ok SET default true;

ALTER TABLE usr ADD organisation text;
ALTER TABLE usr ADD mail_style	character(1);
ALTER TABLE usr ADD note	character(1);
ALTER TABLE usr ADD base_rate	numeric;


-- Superseded by the AWL revision table, but we'll keep it for the time being
CREATE TABLE wrms_revision (
  schema_id  INT4,
  schema_major INT4,
  schema_minor INT4,
  schema_patch INT4,
  schema_name TEXT,
  applied_on  TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp
);

GRANT ALL ON wrms_revision TO PUBLIC;