-- Table for holding the schema version so we can be more structured in future
CREATE TABLE wrms_revision (
   schema_id INT4,
   schema_major INT4,
   schema_minor INT4,
   schema_patch INT4,
   schema_name TEXT,
   applied_on TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp
);
-- The schema_id should always be incremented.  The major / minor / patch level should
-- be incremented as seems appropriate...
INSERT INTO wrms_revision (schema_id, schema_major, schema_minor, schema_patch, schema_name)
                    VALUES(        1,            1,           99,            2, 'Welsh Rarebit' );
GRANT SELECT ON wrms_revision TO general;

-- Required actions for an organisation's requests
CREATE TABLE organisation_action (
   action_id SERIAL PRIMARY KEY,
   org_code INT4 REFERENCES organisation,
   action_description TEXT,
   action_sequence INT4 DEFAULT 0,
   active BOOL DEFAULT TRUE
);
CREATE INDEX organisation_action_sk1 ON organisation_action( org_code, action_sequence, lower(action_description) );

-- This constraint should be there, but is missing(!)
ALTER TABLE usr ADD PRIMARY KEY ( user_no );

-- Support deletion of relationships...
GRANT DELETE ON request_request TO general;

-- And the instances associated with each request
CREATE TABLE request_action (
   request_id INT4 REFERENCES request,
   action_id INT4 REFERENCES organisation_action,
   completed_on TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp,
   updated_by_id INT4 REFERENCES usr( user_no ),
   PRIMARY KEY ( request_id, action_id )
);
CREATE INDEX request_action_sk1 ON request_action( action_id );

GRANT INSERT, UPDATE, SELECT, DELETE ON
  request_action, organisation_action, organisation_action_action_id_seq
  TO general;

-- Put some relational integrity constraints in place...

-- Ensure we don't have these constraints differently defined.
ALTER TABLE usr DROP CONSTRAINT organisation_fk;

ALTER TABLE request DROP CONSTRAINT requester_fk;
ALTER TABLE request DROP CONSTRAINT creator_fk;

ALTER TABLE request_note DROP CONSTRAINT request_id_fk;
ALTER TABLE request_status DROP CONSTRAINT request_id_fk;
ALTER TABLE request_timesheet DROP CONSTRAINT request_id_fk;
ALTER TABLE request_quote DROP CONSTRAINT request_id_fk;
ALTER TABLE request_interested DROP CONSTRAINT request_id_fk;
ALTER TABLE request_allocated DROP CONSTRAINT request_id_fk;
ALTER TABLE request_attachment DROP CONSTRAINT request_id_fk;


-- We have to delete some pre-existing crap, and we're not sure how it got there, but it's
-- meaningless without the parent stuff.
DELETE FROM usr WHERE NOT EXISTS( SELECT 1 FROM organisation WHERE org_code = usr.org_code);

DELETE FROM request WHERE NOT EXISTS( SELECT 1 FROM usr WHERE user_no = request.requester_id);
DELETE FROM request WHERE NOT EXISTS( SELECT 1 FROM usr WHERE user_no = request.entered_by);
DELETE FROM request WHERE NOT EXISTS( SELECT 1 FROM usr WHERE user_no = request.requester_id) ;

DELETE FROM request_note WHERE NOT EXISTS ( SELECT 1 FROM request WHERE request_id = request_note.request_id);
DELETE FROM request_status WHERE NOT EXISTS ( SELECT 1 FROM request WHERE request_id = request_status.request_id);
DELETE FROM request_quote WHERE NOT EXISTS ( SELECT 1 FROM request WHERE request_id = request_quote.request_id);
DELETE FROM request_timesheet WHERE NOT EXISTS ( SELECT 1 FROM request WHERE request_id = request_timesheet.request_id);
DELETE FROM request_interested WHERE NOT EXISTS ( SELECT 1 FROM request WHERE request_id = request_interested.request_id);
DELETE FROM request_allocated WHERE NOT EXISTS ( SELECT 1 FROM request WHERE request_id = request_allocated.request_id);
DELETE FROM request_attachment WHERE NOT EXISTS ( SELECT 1 FROM request WHERE request_id = request_attachment.request_id);


-- Finally, we can (re-)add the constraints
ALTER TABLE request_note ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);
ALTER TABLE request_status ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);
ALTER TABLE request_timesheet ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);
ALTER TABLE request_quote ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);
ALTER TABLE request_interested ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);
ALTER TABLE request_allocated ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);
ALTER TABLE request_attachment ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);

ALTER TABLE request ADD CONSTRAINT requester_fk FOREIGN KEY (requester_id) REFERENCES usr(user_no);
ALTER TABLE request ADD CONSTRAINT creator_fk FOREIGN KEY (entered_by) REFERENCES usr(user_no);

ALTER TABLE usr ADD CONSTRAINT organisation_fk FOREIGN KEY (org_code) REFERENCES organisation(org_code);
