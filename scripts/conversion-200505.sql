-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

ALTER TABLE ugroup ADD PRIMARY KEY (group_no);
ALTER TABLE ugroup ADD UNIQUE (group_name);

ALTER TABLE system_usr DROP CONSTRAINT system_usr_pkey;
DELETE FROM system_usr WHERE system_code = '';
ALTER TABLE system_usr ADD PRIMARY KEY (user_no,system_code);

BEGIN;

SELECT check_wrms_revision(1,99,4);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,5, 'Foccaccia' );

-- The actual changes for this revision
CREATE TABLE fixed_gm
       AS SELECT DISTINCT group_no, user_no
          FROM group_member
          WHERE EXISTS (SELECT 1 FROM usr WHERE usr.user_no = group_member.user_no)
            AND EXISTS (SELECT 1 FROM ugroup WHERE ugroup.group_no = group_member.group_no);

ALTER TABLE group_member RENAME TO old_group_member;
ALTER TABLE fixed_gm RENAME TO group_member;
ALTER TABLE group_member ADD UNIQUE (user_no,group_no);
GRANT SELECT,INSERT,DELETE,UPDATE ON group_member TO general;

GRANT SELECT,INSERT,DELETE,UPDATE ON organisation_action TO general;

ALTER TABLE group_member ADD CONSTRAINT group_fk FOREIGN KEY (group_no) REFERENCES ugroup(group_no);
ALTER TABLE group_member ADD CONSTRAINT user_fk FOREIGN KEY (user_no) REFERENCES usr(user_no);

UPDATE saved_queries SET in_menu = TRUE;

SELECT setval('ugroup_group_no_seq',max(group_no)) FROM ugroup;
ALTER TABLE ugroup ADD COLUMN seq INT4;
UPDATE ugroup SET seq = group_no * 100;

INSERT INTO ugroup (module_name,group_name,seq) VALUES('wrms','Accounts', 230);
INSERT INTO ugroup (module_name,group_name,seq) VALUES('wrms','OrgMgr', 270);

-- Set a user as having a particular system-related role
CREATE or REPLACE FUNCTION set_system_role (int4, text, text ) RETURNS int4 AS '
   DECLARE
      u_no ALIAS FOR $1;
      sys_code ALIAS FOR $2;
      new_role ALIAS FOR $3;
      curr_val TEXT;
   BEGIN
      SELECT role INTO curr_val FROM system_usr
                      WHERE user_no = u_no AND system_code = sys_code;
      IF FOUND THEN
        IF curr_val = new_role THEN
          RETURN u_no;
        ELSE
          UPDATE system_usr SET role = new_role
                      WHERE user_no = u_no AND system_code = sys_code;
        END IF;
      ELSE
        INSERT INTO system_usr (user_no, system_code, role)
                         VALUES( u_no, sys_code, new_role );
      END IF;
      RETURN u_no;
   END;
' LANGUAGE 'plpgsql';

-- And finally commit that to make it a logical unit...
COMMIT;


ALTER TABLE group_member CLUSTER ON group_member_user_no_key;
CLUSTER group_member;

ALTER TABLE system_usr CLUSTER ON system_usr_pkey;
CLUSTER system_usr;

ALTER TABLE request_interested DROP CONSTRAINT request_interested_pkey;
ALTER TABLE request_interested ADD PRIMARY KEY (request_id,user_no);

ALTER TABLE request_interested CLUSTER ON request_interested_pkey;
CLUSTER request_interested;

ALTER TABLE request_note CLUSTER ON request_note_pkey;
CLUSTER request_note;

DROP INDEX xpk_request_status;
ALTER TABLE request_status ADD PRIMARY KEY (request_id,status_on);
ALTER TABLE request_status CLUSTER ON request_status_pkey;
CLUSTER request_status;

ALTER TABLE request_allocated ADD PRIMARY KEY (request_id,allocated_to_id);
ALTER TABLE request_allocated CLUSTER ON request_allocated_pkey;
CLUSTER request_allocated;

DROP INDEX request_attachment_skey;
CREATE INDEX request_attachment_skey ON request_attachment(request_id,attachment_id);
ALTER TABLE request_attachment CLUSTER ON request_attachment_pkey;
CLUSTER request_attachment;

DROP INDEX xak1_request_quote;
CREATE INDEX request_quote_skey ON request_quote(request_id,quote_id);
ALTER TABLE request_quote CLUSTER ON request_quote_pkey;
CLUSTER request_quote;

ALTER TABLE request_action CLUSTER ON request_action_pkey;
CLUSTER request_action;

ALTER TABLE request_tag CLUSTER ON request_tag_pkey;
CLUSTER request_tag;

ALTER TABLE request_request CLUSTER ON request_request_sk1;
CLUSTER request_request;

CREATE INDEX request_timesheet_req ON request_timesheet(request_id,timesheet_id);
ALTER TABLE request_timesheet CLUSTER ON request_timesheet_req;
CLUSTER request_timesheet;

VACUUM FULL VERBOSE ANALYZE group_member;
VACUUM FULL VERBOSE ANALYZE saved_queries;

