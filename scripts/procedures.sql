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


