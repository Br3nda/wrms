ALTER TABLE request ADD COLUMN sla_response_time TIMESPAN;
ALTER TABLE request ADD COLUMN sla_response_type CHAR;
ALTER TABLE request ALTER COLUMN sla_response_type SET DEFAULT 'O';
ALTER TABLE request ADD COLUMN requested_by_date TIMESTAMP;
ALTER TABLE request ADD COLUMN agreed_due_date TIMESTAMP;

UPDATE request SET sla_response_type = 0;
UPDATE request SET sla_response_time = NULL;

ALTER TABLE organisation ADD COLUMN current_sla BOOL;
