-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

BEGIN;

SELECT check_wrms_revision(1,99,11);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,12, 'Tank Bread' );

-- Changes to the usr table for consistency with
-- recent versions of AWL
ALTER TABLE usr RENAME COLUMN last_accessed TO last_used;
ALTER TABLE usr ADD COLUMN active BOOLEAN;
UPDATE usr SET active = (status = 'A');
DROP VIEW organisation_plus;
ALTER TABLE usr DROP COLUMN status;

-- Changes to the groups/roles table for consistency with
-- recent versions of AWL
ALTER TABLE ugroup RENAME TO roles;
ALTER TABLE roles RENAME COLUMN group_no TO role_no;
ALTER TABLE roles RENAME COLUMN group_name TO role_name;
CREATE SEQUENCE roles_role_no_seq;
SELECT setval('roles_role_no_seq',max(role_no)) FROM roles;
ALTER TABLE roles ALTER COLUMN role_no SET DEFAULT nextval('roles_role_no_seq');
-- Truly hairy hack to work around bug present in at least PostgreSQL 8.1.4
DELETE FROM pg_depend WHERE deptype = 'i' AND objid = (SELECT oid FROM pg_class WHERE relname = 'ugroup_group_no_seq');
DROP SEQUENCE ugroup_group_no_seq;
ALTER TABLE group_member RENAME TO role_member;
ALTER TABLE role_member RENAME COLUMN group_no TO role_no;

-- Adding a column to make the hierarchy more explicit so
-- that we can hide (or show) subsidiary requests.
ALTER TABLE request ADD COLUMN parent_request INT4;

-- Add a column to timesheets to put an 'etag' on there which we
-- will use to add CalDAV support for maintaining timesheets.
ALTER TABLE request_timesheet ADD COLUMN dav_etag TEXT;

UPDATE request_timesheet SET work_duration = ((work_quantity * 8)::text || ' hours')::interval(0) WHERE work_units = 'days';
UPDATE request_timesheet SET work_duration = (work_quantity::text || ' hours')::interval(0) WHERE work_units = 'hours';
UPDATE request_timesheet SET dav_etag = md5(timesheet_id||request_id||work_on||work_duration||work_by_id||COALESCE(charged_details,'')||work_description)
                WHERE work_duration IS NOT NULL AND work_units = 'hours' OR work_units = 'days';

-- Theoretically I suppose it won't be unique, but that would likely be bad...
CREATE UNIQUE INDEX request_timesheet_etag_skey ON request_timesheet ( work_by_id, dav_etag );

-- The main event.  Where we store the things the calendar throws at us.
CREATE TABLE caldav_data (
  user_no INT references usr(user_no),
  dav_name TEXT,
  dav_etag TEXT,
  caldav_data TEXT,
  caldav_type TEXT,
  logged_user INT references usr(user_no),

  PRIMARY KEY ( user_no, dav_name )
);

GRANT SELECT,INSERT,UPDATE,DELETE ON caldav_data TO general;

-- And finally commit that to make it a logical unit...
COMMIT;

VACUUM FULL ANALYZE request_timesheet;
VACUUM FULL ANALYZE usr;

-- Update the views and procedures in case they have changed
\i procedures.sql
\i views.sql
