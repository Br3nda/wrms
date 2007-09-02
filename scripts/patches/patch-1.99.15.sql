-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

BEGIN;

SELECT check_wrms_revision(1,99,14);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,15, 'Brioche' );

----------------------------------------------------------------------------------
-- Constraints relating to request_id are following
----------------------------------------------------------------------------------
-- Fix names of foreign keys coming from buggy PostgreSQL version (pre 8.0)
UPDATE pg_constraint SET conname = 'request_id_fk'
                   WHERE confrelid = (SELECT oid FROM pg_class WHERE relname = 'request') AND conname ='$1' AND contype = 'f';

\set table request
\set constraint parent_request_fk
\set field parent_request
-- ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set constraint request_id_fk
\set field request_id

\set table request_action
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_allocated
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_attachment
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Kind of unclean, but there shouldn't be much like this
DELETE FROM request_history WHERE NOT EXISTS( SELECT 1 FROM request WHERE request.request_id = request_history.request_id);
\set table request_history
-- ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_interested
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_note
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_qa_action
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_quote
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_status
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_tag
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_timesheet
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_request
\set constraint request_fk
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set field to_request_id
\set constraint to_request_fk
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set field request_id
\set table request_project
\set constraint fk_request__fk_req_pr_request
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table qa_project_step
\set constraint fk_qa_proj_step_reqid
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE;

-- Some of the ones below this are a little more complicated, since project_id becomes a proxy for request_id
\set field project_id
\set constraint fk_proj_qa_step_project
\set references request_project(request_id)
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES :references ON DELETE RESTRICT ON UPDATE CASCADE;

\set field project_id,qa_step_id
\set table qa_project_step_approval
\set constraint fk_proj_step_approval
\set references qa_project_step(project_id,qa_step_id)
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES :references ON DELETE RESTRICT ON UPDATE CASCADE;

-- This table has a pair of identical constraints.  Nice.
\set table qa_project_approval
\set constraint fk_project_qa_approval_step
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES :references ON DELETE RESTRICT ON UPDATE CASCADE;


----------------------------------------------------------------------------------
-- Constraints relating to org_code now
----------------------------------------------------------------------------------
-- Fix names of foreign keys coming from buggy PostgreSQL version (pre 8.0)
UPDATE pg_constraint SET conname = 'organisation_fk'
                   WHERE confrelid = (SELECT oid FROM pg_class WHERE relname = 'organisation') AND conname ='$1' AND contype = 'f';

\set field org_code
\set table usr
ALTER TABLE :table DROP CONSTRAINT organisation_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES organisation(org_code) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table org_system
ALTER TABLE :table DROP CONSTRAINT organisation_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES organisation(org_code) ON DELETE CASCADE ON UPDATE CASCADE;

\set table organisation_action
ALTER TABLE :table DROP CONSTRAINT organisation_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES organisation(org_code) ON DELETE CASCADE ON UPDATE CASCADE;

\set table organisation_tag
ALTER TABLE :table DROP CONSTRAINT organisation_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES organisation(org_code) ON DELETE CASCADE ON UPDATE CASCADE;


----------------------------------------------------------------------------------
-- Constraints relating to action_id
----------------------------------------------------------------------------------
-- Fix names of foreign keys coming from buggy PostgreSQL version (pre 8.0)
UPDATE pg_constraint SET conname = 'request_action_action_id_fkey'
                   WHERE confrelid = (SELECT oid FROM pg_class WHERE relname = 'organisation_action') AND conname = '$2' AND contype = 'f';

\set constraint request_action_action_id_fkey
\set field action_id
\set table request_action
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES organisation_action(:field) ON DELETE CASCADE ON UPDATE CASCADE;


----------------------------------------------------------------------------------
-- Constraints relating to tag_id
----------------------------------------------------------------------------------
-- Fix names of foreign keys coming from buggy PostgreSQL version (pre 8.0)
UPDATE pg_constraint SET conname = 'request_tag_tag_id_fkey'
                   WHERE confrelid = (SELECT oid FROM pg_class WHERE relname = 'organisation_tag') AND conname = '$2' AND contype = 'f';

\set constraint request_tag_tag_id_fkey
\set field tag_id
\set table request_tag
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES organisation_tag(:field) ON DELETE CASCADE ON UPDATE CASCADE;


----------------------------------------------------------------------------------
-- Constraints relating to user_no now
----------------------------------------------------------------------------------
-- Set the names of all foreign keys - some come from buggy PostgreSQL version (pre 8.0) and have weird names
UPDATE pg_constraint SET conname = 'usr_fk'
                   WHERE confrelid = (SELECT oid FROM pg_class WHERE relname = 'usr') AND contype = 'f';

\set table caldav_data
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field user_no
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;
\set field logged_user
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;

\set table qa_project_approval
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field assigned_to_usr
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;
\set field approval_by_usr
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table qa_project_step
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field responsible_usr
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field entered_by
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;
\set field requester_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_action
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field updated_by_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_allocated
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field allocated_to_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_attachment
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field attached_by
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_note
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field note_by_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_project
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field qa_mentor
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;
\set field project_manager
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_qa_action
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field action_by
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_quote
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field approved_by_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;
\set field quote_by_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_status
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field status_by_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_timesheet
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field charged_by_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;
\set field work_by_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table timesheet_note
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set field note_by_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;


-- These tables all use the field user_no
\set field user_no
\set table request_interested
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table role_member
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;

\set table saved_queries
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;

\set table session
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;

\set table system_usr
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;

\set table tmp_password
ALTER TABLE :table DROP CONSTRAINT usr_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;


----------------------------------------------------------------------------------
-- Constraints relating to system_id now
----------------------------------------------------------------------------------
-- Set the names of all foreign keys - some come from buggy PostgreSQL version (pre 8.0) and have weird names
UPDATE pg_constraint SET conname = 'system_id_fk'
                   WHERE confrelid = (SELECT oid FROM pg_class WHERE relname = 'work_system') AND contype = 'f';

\set field system_id
\set table org_system
ALTER TABLE :table DROP CONSTRAINT system_id_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES work_system(system_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request
ALTER TABLE :table DROP CONSTRAINT system_id_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES work_system(system_id) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table system_usr
ALTER TABLE :table DROP CONSTRAINT system_id_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES work_system(system_id) ON DELETE CASCADE ON UPDATE CASCADE;

\set table organisation
\set field general_system
ALTER TABLE :table DROP CONSTRAINT system_id_fk;
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES work_system(system_id) ON DELETE RESTRICT ON UPDATE CASCADE;



-- And finally commit that to make it a logical unit...
COMMIT;


VACUUM FULL ANALYZE;

CLUSTER request;
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

-- Update the views and procedures in case they have changed
\i dba/procedures.sql
\i dba/views.sql
