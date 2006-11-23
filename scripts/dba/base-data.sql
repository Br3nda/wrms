BEGIN;

-- to have a starting value for nextval()
INSERT INTO request (request_id) VALUES ('-1');

-- create a base organisation
INSERT INTO organisation (org_code, admin_usr) VALUES ('0', 'wrms');

-- create a base user to access WRMS
INSERT INTO usr (user_no, username, password, fullname, org_code) VALUES ('0', 'wrms', 'wrms', 'This is the base WRMS user', '0');

-- create a base work_system
INSERT INTO work_system (system_id, system_code, active) VALUES ('0', 'wrms', true);

COMMIT;