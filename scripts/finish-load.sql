
SELECT setval( 'request_request_id_seq', max_request() )      AS "Request ID";
SELECT setval( 'system_update_update_id_seq', max_update() )  AS " Update ID";
SELECT setval( 'request_quote_quote_id_seq', max_quote() )    AS "  Quote ID";
SELECT setval( 'awm_perorg_perorg_id_seq', awm_max_perorg() ) AS " PerOrg ID";

VACUUM ANALYZE;
