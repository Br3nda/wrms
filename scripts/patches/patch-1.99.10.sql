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
ALTER TABLE organisation DROP COLUMN support_user_no;
ALTER TABLE organisation DROP COLUMN admin_usr;
ALTER TABLE org_system DROP COLUMN admin_user_no;
ALTER TABLE org_system DROP COLUMN support_user_no;

-- Add a numeric ID to work_system which we will eventually use everywhere
ALTER TABLE work_system ADD COLUMN system_id SERIAL;
UPDATE work_system SET system_id = nextval('work_system_system_id_seq');
ALTER TABLE work_system ALTER COLUMN system_id SET NOT NULL;
CREATE UNIQUE INDEX work_system_pk1 ON work_system(system_id);

-- Add a system for DEFUNCT work request attachments
INSERT INTO work_system ( system_code, system_desc, active )
    VALUES( 'zDEFUNCT', 'DEFUNCT Attachments', FALSE );
-- Associate it with the defunct attachments request.
UPDATE request SET system_code = 'zDEFUNCT' WHERE request_id = -1;

-- Propagate the system_id column to the org_system table
ALTER TABLE org_system ADD COLUMN system_id INT4;
DELETE from org_system WHERE NOT EXISTS( SELECT 1 FROM work_system WHERE work_system.system_code = org_system.system_code);
UPDATE org_system SET system_id = work_system.system_id
  FROM work_system WHERE org_system.system_code = work_system.system_code;
ALTER TABLE org_system ALTER COLUMN system_id SET NOT NULL;
CREATE UNIQUE INDEX org_system_fk1 ON org_system(system_id,org_code);
ALTER TABLE org_system ADD CONSTRAINT system_id_fk FOREIGN KEY (system_id) REFERENCES work_system(system_id);

-- Propagate the system_id column to the system_usr table
ALTER TABLE system_usr ADD COLUMN system_id INT4;
DELETE from system_usr WHERE NOT EXISTS( SELECT 1 FROM work_system WHERE work_system.system_code = system_usr.system_code);
UPDATE system_usr SET system_id = work_system.system_id
  FROM work_system WHERE system_usr.system_code = work_system.system_code;
ALTER TABLE system_usr ALTER COLUMN system_id SET NOT NULL;
CREATE UNIQUE INDEX system_usr_fk1 ON system_usr(system_id,user_no);
ALTER TABLE system_usr ADD CONSTRAINT system_id_fk FOREIGN KEY (system_id) REFERENCES work_system(system_id);

-- Propagate the system_id column to the request table
ALTER TABLE request ADD COLUMN system_id INT4;
DELETE from request_attachment WHERE NOT EXISTS( SELECT 1 FROM work_system, request WHERE request.request_id = request_attachment.request_id AND work_system.system_code = request.system_code);
DELETE from request WHERE NOT EXISTS( SELECT 1 FROM work_system WHERE work_system.system_code = request.system_code);
UPDATE request SET system_id = work_system.system_id
  FROM work_system WHERE request.system_code = work_system.system_code;
ALTER TABLE request ALTER COLUMN system_id SET NOT NULL;
ALTER TABLE request ADD CONSTRAINT system_id_fk FOREIGN KEY (system_id) REFERENCES work_system(system_id);

\i procedures.sql

-- Now that we have re-defined things to get rid of our dependence on
-- system_code we can remove it from some places:
ALTER TABLE system_usr DROP COLUMN system_code CASCADE;
ALTER TABLE org_system DROP COLUMN system_code CASCADE;
ALTER TABLE request DROP COLUMN system_code CASCADE;

-- Allow us to write code that marks a system as specific to one organisation
ALTER TABLE work_system ADD COLUMN organisation_specific BOOLEAN DEFAULT FALSE;

-- Allow us to specify a generic system on the organisation
ALTER TABLE organisation ADD COLUMN general_system INT REFERENCES work_system(system_id);

-- And finally commit that to make it a logical unit...
COMMIT;


-- We are less fussed about whether this all succeeds or fails.

-- Delete some pointless / old indexes
DROP INDEX xak0_request;
DROP INDEX xak1_request;
DROP INDEX xak2_request;
DROP INDEX xak3_request;
DROP INDEX xak4_request;

-- Speed up some stuff with a couple of new indexes
CREATE INDEX request_sk0 ON request(request_id) WHERE active;
CREATE INDEX request_sk1 ON request(requester_id) WHERE active;
CREATE INDEX request_sk2 ON request(system_id) WHERE active;
CREATE INDEX request_sk3 ON request(last_status) WHERE active;

VACUUM FULL organisation;
VACUUM FULL work_system;
CLUSTER system_usr;
VACUUM FULL org_system;
VACUUM FULL request;
VACUUM FULL request;

-- The views have changed (well, been created actually :-)
\i views.sql
