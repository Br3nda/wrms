
SELECT setval( 'request_request_id_seq', max_request() );
SELECT setval( 'system_update_update_id_seq', max_update() );
SELECT setval( 'request_quote_quote_id_seq', max_quote() );
SELECT setval( 'awm_perorg_perorg_id_seq', awm_max_perorg() );

UPDATE request SET request_type = 20 WHERE request_type = 1;
UPDATE request SET request_type = 30 WHERE request_type = 2;

VACUUM ANALYZE;
