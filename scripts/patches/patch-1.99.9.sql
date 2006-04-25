-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.


-- IN this case we are not applying a logical unit.  Sadly the QAMS patch doesn't
-- work that straightforwardly :-(

\i qamsdb/qams_core.sql
\i qamsdb/qams_core_data.sql
\i qamsdb/wrms_to_qams.sql

BEGIN;

SELECT check_wrms_revision(1,99,8);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,9, 'Damper' );

-- And finally commit that to make it a logical unit...
COMMIT;

