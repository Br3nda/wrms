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

SELECT check_wrms_revision(1,99,5);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,6, 'Baguette' );

-- And finally commit that to make it a logical unit...
COMMIT;

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
