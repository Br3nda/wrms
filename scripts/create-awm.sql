\r
  This is all 'commented' by the \r statements 
   - keep it in case we need it all again!
  DROP PROCEDURAL LANGUAGE 'plpgsql'
  CREATE FUNCTION plpgsql_call_handler ()
         RETURNS OPAQUE AS '/usr/lib/postgresql/lib/plpgsql.so'
         LANGUAGE 'C'
  CREATE TRUSTED PROCEDURAL LANGUAGE 'plpgsql' HANDLER plpgsql_call_handler LANCOMPILER 'PL/pgSQL'
\r

CREATE FUNCTION name_to_sort_key (text) RETURNS text AS '
   DECLARE
      fullname ALIAS FOR $1;
      sortkey TEXT;
      sp_pos INT4;
   BEGIN
      sp_pos := position( '' '' in fullname);
      sortkey := substr( fullname, sp_pos + 1) || '', '';
      sortkey := sortkey || substr( fullname, 0, sp_pos);
      sortkey := lower( sortkey );
      RETURN sortkey;
   END;
' LANGUAGE 'plpgsql';


CREATE TABLE awm_usr (
  perorg_id INT4,
  validated INT2 DEFAULT 0,
  enabled INT2 DEFAULT 1,
  access_level INT4 DEFAULT 100,
  last_accessed DATETIME,
  username TEXT NOT NULL UNIQUE PRIMARY KEY,
  password TEXT
);
GRANT INSERT,UPDATE,SELECT ON awm_usr TO general;
CREATE INDEX awm_usr_ak1 ON awm_usr ( perorg_id );

CREATE TABLE awm_usr_setting (
  username TEXT,
  setting_name TEXT,
  setting_value TEXT
);
GRANT INSERT,UPDATE,SELECT ON awm_usr_setting TO general;
CREATE UNIQUE INDEX awm_usr_setting_key ON awm_usr_setting ( username, setting_name );

CREATE TABLE awm_usr_group (
  username TEXT,
  group_name TEXT
);
GRANT INSERT,UPDATE,SELECT ON awm_usr_group TO general;
CREATE UNIQUE INDEX awm_usr_group_key ON awm_usr_group ( username, group_name );
CREATE UNIQUE INDEX awm_usr_group_ak1 ON awm_usr_group ( group_name, username );

CREATE TABLE awm_group (
  group_name TEXT UNIQUE PRIMARY KEY,
  group_desc TEXT
);
GRANT INSERT,UPDATE,SELECT ON awm_group TO general;

CREATE TABLE awm_perorg (
  perorg_id SERIAL PRIMARY KEY,
  perorg_name TEXT,
  perorg_sort_key TEXT,
  perorg_type TEXT
);
GRANT INSERT,UPDATE,SELECT ON awm_perorg TO general;
GRANT SELECT ON awm_perorg_perorg_id_seq TO general;
CREATE INDEX awm_perorg_ak1 ON awm_perorg ( perorg_sort_key, perorg_name, perorg_id );
CREATE INDEX awm_perorg_ak2 ON awm_perorg ( perorg_type, perorg_sort_key );

CREATE TABLE awm_perorg_rel (
  perorg_id INT4,
  perorg_rel_id INT4,
  perorg_rel_type TEXT
) ;
GRANT INSERT,UPDATE,SELECT ON awm_perorg_rel TO general;
CREATE INDEX awm_perorg_rel_key ON awm_perorg_rel ( perorg_id, perorg_rel_type );
CREATE INDEX awm_perorg_rel_ak1 ON awm_perorg_rel ( perorg_rel_id, perorg_rel_type );

CREATE TABLE awm_perorg_data (
  perorg_id INT4,
  po_data_name TEXT,
  po_data_value TEXT
) ;
GRANT INSERT,UPDATE,SELECT ON awm_perorg_data TO general;
CREATE UNIQUE INDEX awm_perorg_data_key ON awm_perorg_data ( perorg_id, po_data_name );

CREATE TABLE awm_page (
  page_name TEXT UNIQUE PRIMARY KEY,
  page_desc TEXT,
  page_type TEXT
);
GRANT INSERT,UPDATE,SELECT ON awm_page TO general;

CREATE TABLE awm_content (
  page_name TEXT,
  content_name TEXT,
  content_seq INT4,
  content_type TEXT,
  content_value TEXT
);
CREATE INDEX awm_content_key ON awm_content ( page_name, content_seq, content_name );
CREATE UNIQUE INDEX awm_content_ak1 ON awm_content ( page_name, content_name );
GRANT SELECT,INSERT,UPDATE ON awm_content TO general;

CREATE TABLE lookup_code (
  source_table TEXT,
  source_field TEXT,
  lookup_seq INT2 DEFAULT 0,
  lookup_code TEXT,
  lookup_desc TEXT,
  lookup_misc TEXT
);
CREATE INDEX lookup_code_key ON lookup_code ( source_table, source_field, lookup_seq, lookup_code );
CREATE UNIQUE INDEX lookup_code_ak1 ON lookup_code ( source_table, source_field, lookup_code );
GRANT SELECT ON lookup_code TO PUBLIC;
GRANT SELECT,INSERT,UPDATE ON lookup_code TO general;

CREATE FUNCTION awm_max_perorg()
    RETURNS INT4
    AS 'SELECT max(perorg_id) FROM awm_perorg'
    LANGUAGE 'sql';

CREATE FUNCTION awm_perorg_id_from_name( TEXT )
    RETURNS INT4
    AS 'SELECT perorg_id AS RESULT FROM awm_perorg WHERE perorg_name = $1;'
    LANGUAGE 'sql';

CREATE FUNCTION awm_get_perorg_data( INT4, TEXT )
    RETURNS TEXT
    AS 'SELECT po_data_value AS RESULT FROM awm_perorg_data WHERE perorg_id = $1 AND po_data_name = $2;'
    LANGUAGE 'sql';

CREATE FUNCTION awm_get_rel_parent( INT4, TEXT )
    RETURNS INT4
    AS 'SELECT perorg_id AS RESULT FROM awm_perorg_rel WHERE perorg_rel_id = $1 AND perorg_rel_type = $2;'
    LANGUAGE 'sql';

CREATE FUNCTION awm_get_rel_child( INT4, TEXT )
    RETURNS INT4
    AS 'SELECT perorg_rel_id AS RESULT FROM awm_perorg_rel WHERE perorg_id = $1 AND perorg_rel_type = $2;'
    LANGUAGE 'sql';

CREATE FUNCTION awm_get_lookup_desc( TEXT, TEXT, TEXT )
    RETURNS TEXT
    AS 'SELECT lookup_desc AS RESULT FROM lookup_code 
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;'
    LANGUAGE 'sql';

CREATE FUNCTION awm_get_lookup_misc( TEXT, TEXT, TEXT )
    RETURNS TEXT
    AS 'SELECT lookup_misc AS RESULT FROM lookup_code 
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;'
    LANGUAGE 'sql';

CREATE FUNCTION awm_set_perorg_data (int4, text, text) RETURNS text AS '
   DECLARE
      po_id ALIAS FOR $1;
      data_name ALIAS FOR $2;
      data_value ALIAS FOR $3;
      curr_val TEXT;
   BEGIN
      SELECT po_data_value INTO curr_val FROM awm_perorg_data WHERE perorg_id = po_id AND po_data_name = data_name;
      IF FOUND THEN
        UPDATE awm_perorg_data SET po_data_value = data_value WHERE perorg_id = po_id AND po_data_name = data_name;
      ELSE
        INSERT INTO awm_perorg_data (perorg_id, po_data_name, po_data_value) VALUES( po_id, data_name, data_value);
      END IF;
      RETURN data_value;
   END;
' LANGUAGE 'plpgsql';


CREATE TABLE perorg_system (
   perorg_id INT4,
   persys_role TEXT,
   system_code TEXT
) ;
GRANT INSERT,UPDATE,SELECT ON perorg_system TO general;
CREATE UNIQUE INDEX perorg_system_key ON perorg_system ( perorg_id, persys_role, system_code );
CREATE INDEX perorg_system_ak1 ON perorg_system ( system_code, persys_role );

CREATE FUNCTION is_persys_role( INT4, TEXT, TEXT ) RETURNS BOOLEAN AS '
   DECLARE
      answer BOOLEAN;
      unused INT4;
   BEGIN
      SELECT perorg_id INTO unused FROM perorg_system
                 WHERE perorg_id = $1 AND persys_role = $2 AND system_code = $3;
      IF FOUND THEN
         answer = TRUE;
      ELSE
         answer = FALSE;
      END IF;
      RETURN answer;
   END;
' LANGUAGE 'plpgsql';


CREATE TABLE perorg_request (
   perorg_id INT4,
   request_id INT4,
   perreq_from DATETIME DEFAULT TEXT 'now',
   perreq_role TEXT
) ;
GRANT INSERT,UPDATE,SELECT ON perorg_request TO general;
CREATE UNIQUE INDEX perorg_request_pkey ON perorg_request ( perorg_id, perreq_role, request_id );
CREATE INDEX perorg_request_ak1 ON perorg_request ( request_id, perreq_role );


CREATE FUNCTION get_usr_email(TEXT)
    RETURNS TEXT
    AS 'SELECT po_data_value FROM awm_perorg_data, awm_usr
           WHERE LOWER(awm_usr.username) = LOWER($1)
             AND awm_perorg_data.perorg_id = awm_usr.perorg_id
             AND po_data_name = ''email'' '
    LANGUAGE 'sql';

CREATE FUNCTION set_perreq_role (int4, int4, text) RETURNS text AS '
   DECLARE
      po_id ALIAS FOR $1;
      req_id ALIAS FOR $2;
      role_code ALIAS FOR $3;
      curr_val TEXT;
   BEGIN
      SELECT perreq_role INTO curr_val FROM perorg_request WHERE perorg_id = po_id AND request_id = req_id AND perreq_role = role_code;
      IF NOT FOUND THEN
        INSERT INTO perorg_request (perorg_id, request_id, perreq_role) VALUES( po_id, req_id, role_code);
      END IF;
      RETURN role_code;
   END;
' LANGUAGE 'plpgsql';




