VACUUM ANALYZE request_words;

CREATE INDEX request_words_pkey ON request_words ( string, id );
CREATE INDEX request_oid_skey ON request ( oid );

SELECT setval( 'request_request_id_seq', max_request() )      AS "Request ID";
SELECT setval( 'system_update_update_id_seq', max_update() )  AS " Update ID";
SELECT setval( 'request_quote_quote_id_seq', max_quote() )    AS "  Quote ID";
SELECT setval( 'session_session_id_seq', max_session() )      AS "Session ID";
SELECT setval( 'request_timesh_timesheet_id_seq', max_timesheet()) AS " Timesheet";

VACUUM ANALYZE;
