-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

BEGIN;

SELECT check_wrms_revision(1,99,15);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,16, 'Scone' );

-- Cleaning up some sequence names
CREATE SEQUENCE qa_document_qa_document_id_seq;
SELECT setval('qa_document_qa_document_id_seq', (SELECT last_value FROM seq_qa_document_id));
ALTER TABLE qa_document ALTER COLUMN qa_document_id SET DEFAULT nextval('qa_document_qa_document_id_seq');
DROP SEQUENCE seq_qa_document_id;

CREATE SEQUENCE qa_approval_type_qa_approval_type_id_seq;
SELECT setval('qa_approval_type_qa_approval_type_id_seq', (SELECT last_value FROM seq_qa_approval_type_id));
ALTER TABLE qa_approval_type ALTER COLUMN qa_approval_type_id SET DEFAULT nextval('qa_approval_type_qa_approval_type_id_seq');
DROP SEQUENCE seq_qa_approval_type_id;

CREATE SEQUENCE qa_model_qa_model_id_seq;
SELECT setval('qa_model_qa_model_id_seq', (SELECT last_value FROM seq_qa_model_id));
ALTER TABLE qa_model ALTER COLUMN qa_model_id SET DEFAULT nextval('qa_model_qa_model_id_seq');
DROP SEQUENCE seq_qa_model_id;

CREATE SEQUENCE qa_step_qa_step_id_seq;
SELECT setval('qa_step_qa_step_id_seq', (SELECT last_value FROM seq_qa_step_id));
ALTER TABLE qa_step ALTER COLUMN qa_step_id SET DEFAULT nextval('qa_step_qa_step_id_seq');
DROP SEQUENCE seq_qa_step_id;

-- CREATE SEQUENCE qa_project_approval_qa_approval_id_seq;
SELECT setval('qa_project_approval_qa_approval_id_seq', (SELECT last_value FROM seq_qa_approval_id));
ALTER TABLE qa_project_approval ALTER COLUMN qa_approval_id SET DEFAULT nextval('qa_project_approval_qa_approval_id_seq');
DROP SEQUENCE seq_qa_approval_id;

ALTER TABLE request_history DROP COLUMN system_code;


ALTER TABLE usr_setting ADD COLUMN user_no INT;
UPDATE usr_setting SET user_no = usr.user_no FROM usr WHERE usr.username = usr_setting.username;
ALTER TABLE usr_setting DROP COLUMN username;
DELETE FROM usr_setting WHERE user_no IS NULL;
ALTER TABLE usr_setting ADD CONSTRAINT user_no_fk FOREIGN KEY (user_no) REFERENCES usr (user_no);


-- And finally commit that to make it a logical unit...
COMMIT;

-- Update the views and procedures in case they have changed
\i dba/procedures.sql
\i dba/views.sql
