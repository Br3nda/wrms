CREATE TABLE organisation_tag (
   tag_id SERIAL PRIMARY KEY,
   org_code INT4 REFERENCES organisation,
   tag_description TEXT,
   tag_sequence INT4 DEFAULT 0,
   active BOOL DEFAULT TRUE
);
CREATE INDEX organisation_tag_sk1 ON organisation_tag( tag_sequence, lower(tag_description) );

CREATE TABLE request_tag (
   request_id INT4 REFERENCES request,
   tag_id INT4 REFERENCES organisation_tag,
   tagged_on TIMESTAMP WITH TIME ZONE DEFAULT current_timestamp,
   PRIMARY KEY ( request_id, tag_id )
);
CREATE INDEX request_tag_sk1 ON request_tag( tag_id );

GRANT INSERT, UPDATE, SELECT, DELETE ON
  request_tag, organisation_tag, organisation_tag_tag_id_seq
  TO general;
