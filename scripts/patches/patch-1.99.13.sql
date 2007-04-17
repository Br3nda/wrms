-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

-- This _should_ fail, unless we are retrying this patch, so we keep it outside of the transaction
DROP SEQUENCE qa_project_approval_qa_approval_id_seq;

BEGIN;

SELECT check_wrms_revision(1,99,12);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,13, 'Tortilla' );

CREATE SEQUENCE qa_project_approval_qa_approval_id_seq;
SELECT setval('qa_project_approval_qa_approval_id_seq', qa_approval_id) FROM qa_project_approval ORDER BY qa_approval_id DESC LIMIT 1;
ALTER TABLE qa_project_approval ALTER COLUMN qa_approval_id SET DEFAULT nextval('qa_project_approval_qa_approval_id_seq');

ALTER TABLE usr ADD COLUMN email_ok_time TIMESTAMP;
UPDATE usr SET email_ok_time = current_timestamp WHERE email_ok;
-- Need to CASCADE to drop dependent view, which will be rebuilt after in ./views.sql
ALTER TABLE usr DROP email_ok CASCADE;
ALTER TABLE usr RENAME COLUMN email_ok_time TO email_ok;


-- Add some interesting constraints...
alter table request_allocated
   add constraint usr_fk foreign key (allocated_to_id)
      references usr (user_no)
      on delete restrict on update restrict;

alter table request_attachment
   add constraint usr_fk foreign key (attached_by)
      references usr (user_no)
      on delete restrict on update restrict;

alter table request_note
   add constraint usr_fk foreign key (note_by_id)
      references usr (user_no)
      on delete restrict on update restrict;

alter table request_quote
   add constraint approving_usr_fk foreign key (approved_by_id)
      references usr (user_no)
      on delete restrict on update restrict;

alter table request_quote
   add constraint quoting_usr_fk foreign key (quote_by_id)
      references usr (user_no)
      on delete restrict on update restrict;

-- Requests that don't exist can'r really link to ones that do!
DELETE FROM request_request WHERE NOT EXISTS( SELECT 1 FROM request WHERE request_request.request_id = request.request_id LIMIT 1);
alter table request_request
   add constraint request_fk foreign key (request_id)
      references request (request_id)
      on delete restrict on update restrict;

alter table request_request
   add constraint to_request_fk foreign key (to_request_id)
      references request (request_id)
      on delete restrict on update restrict;

alter table request_timesheet
   add constraint charging_usr_fk foreign key (charged_by_id)
      references usr (user_no)
      on delete restrict on update restrict;

alter table request_timesheet
   add constraint working_usr_fk foreign key (work_by_id)
      references usr (user_no)
      on delete restrict on update restrict;

alter table saved_queries
   add constraint usr_fk foreign key (user_no)
      references usr (user_no)
      on delete restrict on update restrict;

alter table timesheet_note
   add constraint usr_fk foreign key (note_by_id)
      references usr (user_no)
      on delete restrict on update restrict;

-- If we have a record of a user being interested in something, but the user record no longer exists...
DELETE FROM request_interested WHERE NOT EXISTS( SELECT 1 FROM usr WHERE request_interested.user_no = usr.user_no LIMIT 1);
alter table request_interested
   add constraint usr_fk foreign key (user_no)
      references usr (user_no)
      on delete restrict on update restrict;

alter table request_status
   add constraint usr_fk foreign key (status_by_id)
      references usr (user_no)
      on delete restrict on update restrict;

-- System users are kind of useless without the users associated with them.
DELETE FROM system_usr WHERE NOT EXISTS( SELECT 1 FROM usr WHERE system_usr.user_no = usr.user_no LIMIT 1);
alter table system_usr
   add constraint usr_fk foreign key (user_no)
      references usr (user_no)
      on delete restrict on update restrict;

-- We don't care about sessions from users not logged on, let alone from ones who are just not!
DELETE FROM session WHERE NOT EXISTS( SELECT 1 FROM usr WHERE session.user_no = usr.user_no LIMIT 1);
alter table session
   add constraint usr_fk foreign key (user_no)
      references usr (user_no)
      on delete restrict on update restrict;

-- And finally commit that to make it a logical unit...
COMMIT;

DELETE FROM org_system WHERE NOT EXISTS( SELECT 1 FROM organisation WHERE organisation.org_code = org_system.org_code LIMIT 1);
alter table org_system
   add constraint organisation_fk foreign key (org_code)
      references organisation (org_code)
      on delete restrict on update restrict;


VACUUM FULL ANALYZE request_timesheet;
VACUUM FULL ANALYZE usr;

-- Update the views and procedures in case they have changed
\i dba/procedures.sql
\i dba/views.sql

