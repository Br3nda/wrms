create sequence request_attachment_attachment_id_seq;
SELECT setval( 'request_attachment_attachment_id_seq', max_attachment() );
GRANT INSERT, UPDATE, SELECT ON request_attachment_attachment_id_seq TO general;
alter table request_attachment alter column attachment_id set default nextval('request_attachment_attachment_id_seq');

create sequence request_timesheet_timesheet_id_seq;
SELECT setval( 'request_timesheet_timesheet_id_seq', max_timesheet() );
GRANT INSERT, UPDATE, SELECT ON request_timesheet_timesheet_id_seq TO general;
alter table request_timesheet alter column timesheet_id set default nextval('request_timesheet_timesheet_id_seq');

