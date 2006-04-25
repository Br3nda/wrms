-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

BEGIN;

SELECT check_wrms_revision(1,99,9);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,10, 'Damper' );

-- These fields haven't been used for anything in a loong time - pre wrms1, in fact
ALTER TABLE work_system DROP COLUMN notify_usr;
ALTER TABLE work_system DROP COLUMN support_user_no;

-- Allow us to write code that marks a system as specific to one organisation
ALTER TABLE work_system ADD COLUMN organisation_specific BOOLEAN DEFAULT FALSE;

-- And finally commit that to make it a logical unit...
COMMIT;

