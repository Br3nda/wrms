-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

-- This should fail, unless we are retrying this patch, so we keep it outside of the transaction
DROP SEQUENCE qa_project_approval_qa_approval_id_seq;

BEGIN;

SELECT check_wrms_revision(1,99,12);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,13, 'Brioche' );

CREATE SEQUENCE qa_project_approval_qa_approval_id_seq;
SELECT setval('qa_project_approval_qa_approval_id_seq', qa_approval_id) FROM qa_project_approval ORDER BY qa_approval_id DESC LIMIT 1;
ALTER TABLE qa_project_approval ALTER COLUMN qa_approval_id SET DEFAULT nextval('qa_project_approval_qa_approval_id_seq');



-- And finally commit that to make it a logical unit...
COMMIT;

VACUUM FULL ANALYZE request_timesheet;
VACUUM FULL ANALYZE usr;

-- Update the views and procedures in case they have changed
\i procedures.sql
\i views.sql
