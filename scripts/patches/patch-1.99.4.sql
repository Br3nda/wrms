-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

BEGIN;

SELECT check_wrms_revision(1,99,3);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,4, 'Garlic Bread' );

-- The actual changes for this revision
ALTER TABLE saved_queries ADD COLUMN in_menu BOOLEAN ;
ALTER TABLE saved_queries ALTER COLUMN in_menu SET DEFAULT FALSE;

-- Make false the default ok_to_charge value.
ALTER TABLE request_timesheet ALTER COLUMN ok_to_charge SET DEFAULT FALSE;
UPDATE request_timesheet SET ok_to_charge = false WHERE ok_to_charge IS NULL;

-- Add a default base_rate to hold a person's base chargeable rate
ALTER TABLE usr ADD COLUMN base_rate NUMERIC;

-- And finally commit that to make it a logical unit...
COMMIT;

VACUUM FULL VERBOSE ANALYZE request_timesheet;
