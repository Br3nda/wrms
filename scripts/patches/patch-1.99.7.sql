-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

BEGIN;

SELECT check_wrms_revision(1,99,6);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,7, 'Ciabatta' );

CREATE TABLE request_qa_action (
  request_id INT4 REFERENCES request ( request_id ),
  action_on TIMESTAMP DEFAULT current_timestamp,
  action_by INT4 REFERENCES usr ( user_no ),
  action_detail TEXT,
  PRIMARY KEY ( request_id, action_on )
);

GRANT SELECT,INSERT ON request_qa_action to general;

-- And finally commit that to make it a logical unit...
COMMIT;

