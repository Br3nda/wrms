-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

CREATE TEMP TABLE single_org_systems AS
  SELECT system_id, count(1) FROM work_system JOIN org_system USING ( system_id ) GROUP BY system_id HAVING count(1) = 1;
CREATE TEMP TABLE single_system_orgs AS
  SELECT org_code, count(1) FROM organisation JOIN org_system USING ( org_code ) GROUP BY org_code HAVING count(1) = 1;
CREATE TEMP TABLE singletons AS
  SELECT system_id, org_code FROM single_system_orgs JOIN org_system USING(org_code) JOIN single_org_systems USING(system_id);

CREATE TEMP TABLE coordinatable AS
  SELECT system_id, count(1) FROM singletons JOIN system_usr USING (system_id) WHERE role = 'C' GROUP BY system_id HAVING count(1) = 1;

CREATE TEMP TABLE coordinators AS
  SELECT user_no, org_code, system_id
    FROM coordinatable JOIN system_usr USING(system_id) JOIN usr USING(user_no)
   WHERE role = 'C';


BEGIN;

SELECT check_wrms_revision(1,99,10);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,11, 'Fruit Loaf' );

UPDATE work_system
   SET organisation_specific = TRUE
 WHERE EXISTS(SELECT 1 FROM singletons WHERE singletons.system_id = work_system.system_id);

-- Possibly this won't work against 7.4?
UPDATE organisation
   SET general_system = singletons.system_id
  FROM singletons
 WHERE singletons.org_code = organisation.org_code ;

-- Ditto...
UPDATE organisation
   SET admin_user_no = usr.user_no
  FROM coordinators
 WHERE coordinators.org_code = organisation.org_code;

-- And finally commit that to make it a logical unit...
COMMIT;


-- Update the views and procedures in case they have changed
\i procedures.sql
\i views.sql
