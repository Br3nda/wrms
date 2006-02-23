-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

BEGIN;

SELECT check_wrms_revision(1,99,7);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,8, 'Turkish' );

CREATE TABLE tmp_password (
  user_no INT4 REFERENCES usr ( user_no ),
  password TEXT,
  valid_until TIMESTAMPTZ DEFAULT (current_timestamp + '1 day'::interval)
);

GRANT SELECT,INSERT,UPDATE,DELETE ON tmp_password TO general;

-- And finally commit that to make it a logical unit...
COMMIT;

