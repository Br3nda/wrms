-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

-- This _should_ fail, unless we are retrying this patch, so we keep it outside of the transaction
\echo The following statement will generate an error _unless_ this patch is being retried
DROP SEQUENCE qa_project_approval_qa_approval_id_seq;

BEGIN;

SELECT check_wrms_revision(1,99,14);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,15, 'Brioche' );

-- Set all foreign key constraints on the 'request' table to cascade updates and be deferrable.
--UPDATE pg_constraint SET confupdtype = 'c', confdeltype = 'r', condeferred = TRUE, condeferrable = TRUE
--                   WHERE confrelid = (SELECT oid FROM pg_class WHERE relname = 'request') AND contype = 'f';

-- Get rid of some stupidly named foreign keys, since we're screwing with this stuff anyway.
UPDATE pg_constraint SET conname = 'request_id_fk'
                   WHERE confrelid = (SELECT oid FROM pg_class WHERE relname = 'request') AND conname ='$1' AND contype = 'f';

\set table request
\set constraint parent_request_fk
\set field parent_request
-- ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set constraint request_id_fk
\set field request_id

\set table request_action
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_allocated
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_attachment
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_history
-- ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_interested
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_note
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_qa_action
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_quote
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_status
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_tag
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_timesheet
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table request_request
\set constraint request_fk
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set field to_request_id
\set constraint to_request_fk
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set field request_id
\set table request_project
\set constraint fk_request__fk_req_pr_request
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set table qa_project_step
\set constraint fk_qa_proj_step_reqid
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES request(request_id) ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

-- Some of the ones below this are a little more complicated, since project_id becomes a proxy for request_id
\set field project_id
\set constraint fk_proj_qa_step_project
\set references request_project(request_id)
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES :references ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

\set field project_id,qa_step_id
\set table qa_project_step_approval
\set constraint fk_proj_step_approval
\set references qa_project_step(project_id,qa_step_id)
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES :references ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

-- This table has a pair of identical constraints.  Nice.
\set table qa_project_approval
\set constraint fk_proj_qa_approval_step
ALTER TABLE :table DROP CONSTRAINT :constraint;
\set constraint fk_project_qa_approval_step
ALTER TABLE :table DROP CONSTRAINT :constraint;
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES :references ON DELETE RESTRICT ON UPDATE CASCADE DEFERRABLE INITIALLY DEFERRED;

-- And finally commit that to make it a logical unit...
COMMIT;


VACUUM FULL ANALYZE;

CLUSTER role_member;
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

-- Update the views and procedures in case they have changed
\i dba/procedures.sql
\i dba/views.sql
