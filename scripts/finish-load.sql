
SELECT setval( 'usr_user_no_seq', max_usr() )                      AS "   User No";
SELECT setval( 'organisation_org_code_seq', max_organisation() )   AS " Org. Code";
SELECT setval( 'request_request_id_seq', max_request() )           AS "Request ID";
SELECT setval( 'request_attac_attachment_id_seq', max_attachment() )       AS " Attach ID";
SELECT setval( 'request_attachment_attachment_id_seq', max_attachment() )       AS " Attach ID";
SELECT setval( 'request_quote_quote_id_seq', max_quote() )         AS "  Quote ID";
SELECT setval( 'session_session_id_seq', max_session() )           AS "Session ID";
SELECT setval( 'request_timesheet_timesheet_id_seq', max_timesheet()) AS " Timesheet";

VACUUM ANALYZE;
