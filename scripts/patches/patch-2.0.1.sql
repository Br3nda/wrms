-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

CREATE or REPLACE FUNCTION request_tags( INT ) RETURNS TEXT AS '
   DECLARE
      req_id ALIAS FOR $1;
      taglist TEXT DEFAULT '''';
      thistag RECORD;
   BEGIN
     FOR thistag IN SELECT tag_description FROM request_tag NATURAL JOIN organisation_tag WHERE request_id = req_id LOOP
       IF taglist != '''' THEN
         taglist = taglist || '', '';
       END IF;
       taglist = taglist || thistag.tag_description;
     END LOOP;
     RETURN taglist;
   END;
' LANGUAGE 'plpgsql';


BEGIN;

SELECT check_wrms_revision(2,0,0);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(2,0,1, 'Baguette' );

ALTER TABLE organisation DROP COLUMN admin_user_no;
ALTER TABLE organisation DROP COLUMN support_user_no;
ALTER TABLE organisation DROP COLUMN admin_usr;

ALTER TABLE usr DROP COLUMN access_level;
ALTER TABLE usr DROP COLUMN linked_user;

-- Change the "enabled" and "validated" columns to booleans...
ALTER TABLE usr ADD COLUMN enabled_new BOOLEAN;
UPDATE usr SET enabled_new = (enabled > 0) ;
ALTER TABLE usr DROP COLUMN enabled;
ALTER TABLE usr RENAME enabled_new TO enabled;
ALTER TABLE usr ALTER COLUMN enabled SET DEFAULT TRUE;

ALTER TABLE usr ADD COLUMN validated_new BOOLEAN;
UPDATE usr SET validated_new = (validated > 0) ;
ALTER TABLE usr DROP COLUMN validated;
ALTER TABLE usr RENAME validated_new TO validated;
ALTER TABLE usr ALTER COLUMN validated SET DEFAULT FALSE;

ALTER TABLE usr DROP COLUMN organisation;

ALTER TABLE ugroup DROP COLUMN module_name;

ALTER TABLE work_system DROP COLUMN support_user_no;
ALTER TABLE work_system DROP COLUMN notify_usr;

ALTER TABLE org_system DROP COLUMN admin_user_no;
ALTER TABLE org_system DROP COLUMN support_user_no;

DROP TABLE usr_setting;
DROP FUNCTION get_request_org(integer);
DROP FUNCTION get_usr_setting(text, text);

ALTER TABLE request DROP COLUMN request_by;
ALTER TABLE request_history DROP COLUMN request_by;

ALTER TABLE request_status DROP COLUMN status_by;
ALTER TABLE request_note DROP COLUMN note_by;
ALTER TABLE request_quote DROP COLUMN quoted_by;
ALTER TABLE request_allocated DROP COLUMN allocated_to;
ALTER TABLE request_interested DROP COLUMN username;
ALTER TABLE request_timesheet DROP COLUMN work_by;



-- And finally commit that to make it a logical unit...
COMMIT;

DROP TABLE old_group_member;
DROP TABLE module;

CLUSTER group_member;
CLUSTER system_usr;
CLUSTER request_interested;
CLUSTER request_note;
CLUSTER request_status;
CLUSTER request_allocated;
CLUSTER request_attachment;
CLUSTER request_quote;
CLUSTER request_action;
CLUSTER request_tag;
CLUSTER request_request;
CLUSTER request_timesheet;
