-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

ALTER TABLE ugroup ADD PRIMARY KEY (group_no);
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
GRANT SELECT,INSERT,DELETE,UPDATE ON group_member TO general;

ALTER TABLE group_member ADD CONSTRAINT group_fk FOREIGN KEY (group_no) REFERENCES ugroup(group_no);
ALTER TABLE group_member ADD CONSTRAINT user_fk FOREIGN KEY (user_no) REFERENCES usr(user_no);

UPDATE saved_queries SET in_menu = TRUE;

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

VACUUM FULL VERBOSE ANALYZE group_member;
VACUUM FULL VERBOSE ANALYZE saved_queries;
