-- Set a user as interested in a request
DROP FUNCTION set_interested (int4, int4);
CREATE FUNCTION set_interested (int4, int4) RETURNS int4 AS '
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
DROP FUNCTION set_allocated (int4, int4);
CREATE FUNCTION set_allocated (int4, int4) RETURNS int4 AS '
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

-- Get the type of a column
DROP FUNCTION column_type( TEXT, TEXT );
CREATE FUNCTION column_type( TEXT, TEXT ) RETURNS TEXT AS '
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


DROP FUNCTION last_status_on( INT4 );
CREATE FUNCTION last_status_on( INT4 ) RETURNS TIMESTAMP AS '
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


DROP FUNCTION help_hit( INT4, TEXT );
CREATE FUNCTION help_hit( INT4, TEXT ) RETURNS INT4 AS '
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


DROP FUNCTION cast_vote (int4, int4, int4, text);
CREATE FUNCTION cast_vote (int4, int4, int4, text) RETURNS int4 AS '
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

DROP FUNCTION user_votes (int4, int4, int4 );
CREATE FUNCTION user_votes (int4, int4, int4 ) RETURNS int4 AS '
    DECLARE
      n_id ALIAS FOR $1;
      w_u_id ALIAS FOR $2;
      v_u_id ALIAS FOR $3;
      RETURN plus_votes + minus_votes;
    BEGIN
      SELECT vote_amount INTO votes FROM wu_vote
              WHERE node_id = n_id AND wu_by = w_u_id AND vote_by = v_u_id LIMIT 1;
      IF NOT FOUND THEN
        votes = 0;
      END IF;
      RETURN votes;
    END;
' LANGUAGE 'plpgsql';
