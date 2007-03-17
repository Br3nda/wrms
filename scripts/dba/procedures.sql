-- Set a user as interested in a request
CREATE or REPLACE FUNCTION set_interested (int4, int4) RETURNS int4 AS '
   DECLARE
      u_no ALIAS FOR $1;
      req_id ALIAS FOR $2;
      curr_val TEXT;
   BEGIN
      SELECT username INTO curr_val FROM request_interested
                      WHERE user_no = u_no AND request_id = req_id;
      IF NOT FOUND THEN
        INSERT INTO request_interested (user_no, request_id, username)
            SELECT user_no, req_id, username FROM usr WHERE user_no = u_no;
      END IF;
      RETURN u_no;
   END;
' LANGUAGE 'plpgsql';

-- Set a user as allocated to a request
CREATE or REPLACE FUNCTION set_allocated (int4, int4) RETURNS int4 AS '
   DECLARE
      u_no ALIAS FOR $1;
      req_id ALIAS FOR $2;
      curr_val TEXT;
   BEGIN
      SELECT allocated_to INTO curr_val FROM request_allocated
                      WHERE allocated_to_id = u_no AND request_id = req_id;
      IF NOT FOUND THEN
        INSERT INTO request_allocated (allocated_to_id, request_id, allocated_to)
            SELECT user_no, req_id, username FROM usr WHERE user_no = u_no;
      END IF;
      RETURN u_no;
   END;
' LANGUAGE 'plpgsql';

-- Set a user as having a particular system-related role
-- DROP FUNCTION set_system_role (int4, text, text );
CREATE or REPLACE FUNCTION set_system_role (int4, int4, text ) RETURNS int4 AS '
   DECLARE
      u_no ALIAS FOR $1;
      sys_id ALIAS FOR $2;
      new_role ALIAS FOR $3;
      curr_val TEXT;
   BEGIN
      SELECT role INTO curr_val FROM system_usr
                      WHERE user_no = u_no AND system_id = sys_id;
      IF FOUND THEN
        IF curr_val = new_role THEN
          RETURN u_no;
        ELSE
          UPDATE system_usr SET role = new_role
                      WHERE user_no = u_no AND system_id = sys_id;
        END IF;
      ELSE
        INSERT INTO system_usr (user_no, system_id, role)
                         VALUES( u_no, sys_id, new_role );
      END IF;
      RETURN u_no;
   END;
' LANGUAGE 'plpgsql';

-- Get the type of a column
CREATE or REPLACE FUNCTION column_type( TEXT, TEXT ) RETURNS TEXT AS '
  DECLARE
    t_name ALIAS FOR $1;
    c_name ALIAS FOR $2;
    table_oid OID;
    attribute_oid OID;
    type_name TEXT;
  BEGIN
    SELECT oid INTO table_oid FROM pg_class WHERE relname = t_name;
    IF NOT FOUND THEN
      RETURN NULL;
    END IF;
    SELECT atttypid INTO attribute_oid FROM pg_attribute
           WHERE attrelid = table_oid AND attname = c_name;
    IF NOT FOUND THEN
      RETURN NULL;
    END IF;
    SELECT typname INTO type_name FROM pg_type
           WHERE pg_type.oid = attribute_oid;
    RETURN  type_name;
  END;
' LANGUAGE 'plpgsql';


CREATE or REPLACE FUNCTION last_status_on( INT4 ) RETURNS TIMESTAMP AS '
  DECLARE
    res TIMESTAMP;
  BEGIN
    SELECT status_on INTO res
          FROM request_status
          WHERE request_status.request_id = $1
            ORDER BY status_on DESC LIMIT 1;
    RETURN res;
  END;
' LANGUAGE 'plpgsql';


CREATE or REPLACE FUNCTION help_hit( INT4, TEXT ) RETURNS INT4 AS '
  DECLARE
    in_user_no ALIAS FOR $1;
    in_topic ALIAS FOR $2;
    out_times INT4;
  BEGIN
    SELECT COALESCE(times,1) INTO out_times FROM help_hit WHERE user_no = in_user_no AND topic = in_topic;
    IF FOUND THEN
      out_times := out_times + 1;
      UPDATE help_hit SET times = out_times, last = now() WHERE user_no = in_user_no AND topic = in_topic;
    ELSE
      INSERT INTO help_hit (user_no, topic, times, last) VALUES(in_user_no, in_topic, 1, now());
      out_times := 1;
    END IF;
    RETURN out_times;
  END;
' LANGUAGE 'plpgsql';


CREATE or REPLACE FUNCTION cast_vote (int4, int4, int4, text) RETURNS int4 AS '
    DECLARE
      n_id ALIAS FOR $1;
      w_u_id ALIAS FOR $2;
      v_u_id ALIAS FOR $3;
      vote ALIAS FOR $4;
      plus_votes INT4;
      minus_votes INT4;
      this_vote INT4;
    BEGIN
      -- Should really set something up in a codes table defining these values.
      IF vote = ''-'' THEN
        this_vote = -1;
      ELSE
        IF vote = ''C'' THEN
          this_vote = 5;
        ELSE
          IF vote = ''K'' THEN
            this_vote = -5;
          ELSE
            this_vote = 1;
          END IF;
        END IF;
      END IF;
      INSERT INTO wu_vote( node_id, wu_by, vote_by, vote_amount, flag)
              VALUES( n_id, w_u_id, v_u_id, this_vote, vote );

      SELECT SUM( vote_amount ) INTO plus_votes FROM wu_vote
              WHERE node_id = n_id AND wu_by = w_u_id AND vote_amount > 0;
      UPDATE wu SET votes_plus = plus_votes WHERE node_id = n_id AND wu_by = w_u_id;

      SELECT SUM( vote_amount ) INTO minus_votes FROM wu_vote
              WHERE node_id = n_id AND wu_by = w_u_id AND vote_amount < 0;
      UPDATE wu SET votes_minus = minus_votes WHERE node_id = n_id AND wu_by = w_u_id;

      RETURN plus_votes + minus_votes;
    END;
' LANGUAGE 'plpgsql';

CREATE or REPLACE FUNCTION user_votes (int4, int4, int4 ) RETURNS int4 AS '
    DECLARE
      n_id ALIAS FOR $1;
      w_u_id ALIAS FOR $2;
      v_u_id ALIAS FOR $3;
      votes INT4;
    BEGIN
      SELECT vote_amount INTO votes FROM wu_vote
              WHERE node_id = n_id AND wu_by = w_u_id AND vote_by = v_u_id LIMIT 1;
      IF NOT FOUND THEN
        votes := 0;
      END IF;
      RETURN votes;
    END;
' LANGUAGE 'plpgsql';

-- The last date a request was made for a particular organisation
CREATE or REPLACE FUNCTION last_org_request ( int4 ) RETURNS timestamp AS '
   DECLARE
      in_org_code ALIAS FOR $1;
      out_date TIMESTAMP;
   BEGIN
      SELECT request_on INTO out_date FROM request, usr
                WHERE request.requester_id = usr.user_no AND request.active
                ORDER BY request.request_on DESC LIMIT 1;
      IF NOT FOUND THEN
        RETURN NULL;
      END IF;
      RETURN out_date;
   END;
' LANGUAGE 'plpgsql' STABLE;

-- The number of requests active for a particular organisation
CREATE or REPLACE FUNCTION active_org_requests ( int4 ) RETURNS int4 AS '
  SELECT count(request_id)::int4 FROM request, usr
    WHERE usr.org_code = $1
      AND request.requester_id = usr.user_no
      AND request.active
      AND last_status NOT IN (''F'', ''C'');
' LANGUAGE 'sql' STABLE;

-- Set a request to a new(?) status
CREATE or REPLACE FUNCTION set_request_status(int4, int4, text ) RETURNS text AS '
   DECLARE
      r_no ALIAS FOR $1;
      changed_by ALIAS FOR $2;
      new_status ALIAS FOR $3;
      curr_val TEXT;
   BEGIN
      SELECT last_status INTO curr_val FROM request WHERE request_id = r_no;
      IF FOUND THEN
        IF curr_val = new_status THEN
          RETURN curr_val;
        ELSE
          UPDATE request SET last_status = new_status, last_activity = current_timestamp
                         WHERE request_id = r_no;
          INSERT INTO request_status (request_id, status_on, status_by_id, status_code)
                           VALUES( r_no, current_timestamp, changed_by, new_status);
        END IF;
      ELSE
        RAISE EXCEPTION ''No such request "%"'', r_no;
      END IF;
      RETURN new_status;
   END;
' LANGUAGE 'plpgsql';

-- Set a request to a new(?) status
CREATE or REPLACE FUNCTION set_request_status(int4, int4, text, boolean ) RETURNS text AS '
   DECLARE
      r_no ALIAS FOR $1;
      changed_by ALIAS FOR $2;
      new_status ALIAS FOR $3;
      new_active ALIAS FOR $4;
      curr_val TEXT;
      curr_active BOOLEAN;
   BEGIN
      IF new_active IS NULL THEN
        RETURN set_request_status( r_no, changed_by, new_status );
      END IF;
      SELECT last_status, active INTO curr_val, curr_active FROM request WHERE request_id = r_no;
      IF FOUND THEN
        IF curr_val = new_status THEN
          IF new_active != curr_active THEN
            UPDATE request SET active = new_active, last_activity = current_timestamp
                           WHERE request_id = r_no;
          END IF;
          RETURN curr_val;
        ELSE
          UPDATE request SET last_status = new_status, active = new_active, last_activity = current_timestamp
                          WHERE request_id = r_no;
          INSERT INTO request_status (request_id, status_on, status_by_id, status_code)
                           VALUES( r_no, current_timestamp, changed_by, new_status);
        END IF;
      ELSE
        RAISE EXCEPTION ''No such request "%"'', r_no;
      END IF;
      RETURN new_status;
   END;
' LANGUAGE 'plpgsql';

CREATE or REPLACE FUNCTION request_tags( INT ) RETURNS TEXT AS '
   DECLARE
      req_id ALIAS FOR $1;
      taglist TEXT DEFAULT '''';
      thistag RECORD;
   BEGIN
     FOR thistag IN SELECT tag_description FROM request_tag NATURAL JOIN organisation_tag WHERE request_id = req_id LOOP
       IF taglist != '''' THEN
         taglist = taglist || '', '';
       END IF;
       taglist = taglist || thistag.tag_description;
     END LOOP;
     RETURN taglist;
   END;
' LANGUAGE 'plpgsql';



CREATE OR REPLACE FUNCTION active_request(INT4)
    RETURNS BOOL
    AS 'SELECT active FROM request WHERE request.request_id = $1' LANGUAGE 'sql';
CREATE OR REPLACE FUNCTION max_request()
    RETURNS INT4
    AS 'SELECT max(request_id) FROM request' LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION request_sla_code(INTERVAL,CHAR)
    RETURNS TEXT
    AS 'SELECT text( date_part( ''hour'', $1) ) || ''|'' || text(CASE WHEN $2 ='' '' THEN ''O'' ELSE $2 END)
    ' LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION get_last_note_on(INT4)
    RETURNS TIMESTAMP
    AS 'SELECT max(note_on)::timestamp FROM request_note WHERE request_note.request_id = $1
    ' LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION get_lookup_desc( TEXT, TEXT, TEXT )
    RETURNS TEXT
    AS 'SELECT lookup_desc AS RESULT FROM lookup_code
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;' LANGUAGE 'sql';

CREATE OR REPLACE FUNCTION get_lookup_misc( TEXT, TEXT, TEXT )
    RETURNS TEXT
    AS 'SELECT lookup_misc AS RESULT FROM lookup_code
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;' LANGUAGE 'sql';


CREATE OR REPLACE FUNCTION get_status_desc(CHAR)
    RETURNS TEXT
    AS 'SELECT lookup_desc AS status_desc FROM lookup_code
            WHERE source_table=''request'' AND source_field=''status_code''
            AND lower(lookup_code) = lower($1)
    ' LANGUAGE 'sql';


CREATE or REPLACE FUNCTION check_wrms_revision( INT, INT, INT ) RETURNS BOOLEAN AS '
   DECLARE
      major ALIAS FOR $1;
      minor ALIAS FOR $2;
      patch ALIAS FOR $3;
      matching INT;
   BEGIN
      SELECT COUNT(*) INTO matching FROM wrms_revision
                      WHERE schema_major = major AND schema_minor = minor AND schema_patch = patch;
      IF matching != 1 THEN
        RAISE EXCEPTION ''Database has not been upgraded to %.%.%'', major, minor, patch;
        RETURN FALSE;
      END IF;
      SELECT COUNT(*) INTO matching FROM wrms_revision
             WHERE (schema_major = major AND schema_minor = minor AND schema_patch > patch)
                OR (schema_major = major AND schema_minor > minor)
                OR (schema_major > major)
             ;
      IF matching >= 1 THEN
        RAISE EXCEPTION ''Database revisions after %.%.% have already been applied.'', major, minor, patch;
        RETURN FALSE;
      END IF;
      RETURN TRUE;
   END;
' LANGUAGE 'plpgsql' STABLE;

CREATE or REPLACE FUNCTION new_wrms_revision( INT, INT, INT, TEXT ) RETURNS BOOLEAN AS '
   DECLARE
      major ALIAS FOR $1;
      minor ALIAS FOR $2;
      patch ALIAS FOR $3;
      blurb ALIAS FOR $4;
      new_id INT;
   BEGIN
      SELECT MAX(schema_id) + 1 INTO new_id FROM wrms_revision;
      IF NOT FOUND THEN
        RAISE EXCEPTION ''Database has no release history!'';
        RETURN FALSE;
      END IF;
      INSERT INTO wrms_revision (schema_id, schema_major, schema_minor, schema_patch, schema_name)
                    VALUES( new_id, major, minor, patch, blurb );
      RETURN TRUE;
   END;
' LANGUAGE 'plpgsql';

CREATE or REPLACE FUNCTION default_timesheet_time( INT, TIMESTAMP ) RETURNS TIMESTAMP AS '
  DECLARE
    in_user ALIAS FOR $1;
    in_time ALIAS FOR $2;
    next_work TIMESTAMP;
  BEGIN
    IF date_trunc(''day'', in_time) != in_time THEN
      RETURN in_time;
    END IF;
    SELECT work_on + work_duration INTO next_work FROM request_timesheet
         WHERE work_by_id = in_user AND work_on >= in_time AND work_on < (in_time + ''1 day''::interval)
         ORDER BY work_on DESC LIMIT 1;
    IF NOT FOUND THEN
      next_work := in_time + ''9 hours''::interval;
    END IF;
      RETURN next_work;
  END;
' LANGUAGE 'plpgsql';