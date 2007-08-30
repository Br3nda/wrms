-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

-- This _should_ fail, unless we are retrying this patch, so we keep it outside of the transaction
\echo The following statement will generate an error _unless_ this patch is being retried
DROP SEQUENCE qa_project_approval_qa_approval_id_seq;

BEGIN;

SELECT check_wrms_revision(1,99,13);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,14, 'Hardtack' );

--
-- Rename the unused admin_user_no field to org_type
ALTER TABLE organisation ADD COLUMN org_type INT;
ALTER TABLE organisation ALTER COLUMN org_type SET DEFAULT 0;

--  We will use three initial codes:  0 = Client, 1 = Support, 2 = Contractor
CREATE TABLE organisation_types (
  org_type INT PRIMARY KEY,
  type_name TEXT
);
INSERT INTO organisation_types VALUES( 0, 'Client' );
INSERT INTO organisation_types VALUES( 1, 'Support' );
INSERT INTO organisation_types VALUES( 2, 'Contractor' );

-- Set the default
UPDATE organisation SET org_type = 0;

ALTER TABLE organisation ADD CONSTRAINT organisation_type_fk
      FOREIGN KEY (org_type) REFERENCES organisation_types(org_type)
      ON DELETE RESTRICT ON UPDATE RESTRICT;

-- Find the contractor organisations
UPDATE organisation SET org_type = 2 WHERE EXISTS( SELECT 1 FROM roles JOIN role_member USING ( role_no ) JOIN usr USING (user_no) WHERE usr.org_code = organisation.org_code AND role_name = 'Contractor');

-- Find the support organisations
UPDATE organisation SET org_type = 1 WHERE EXISTS( SELECT 1 FROM roles JOIN role_member USING ( role_no ) JOIN usr USING (user_no) WHERE usr.org_code = organisation.org_code AND role_name = 'Support');


-- And finally commit that to make it a logical unit...
COMMIT;


VACUUM FULL ANALYZE request_timesheet;
VACUUM FULL ANALYZE usr;
VACUUM FULL ANALYZE organisation;

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

