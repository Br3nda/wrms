-- We do everything in a transaction now, which enables two things:
--  1) We can check for the correct version to upgrade, and fail
--     if we are at the wrong version.
--  2) We apply the whole lot, or none at all, which should make
--     updates more consistent.

BEGIN;

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
' LANGUAGE 'plpgsql';
SELECT check_wrms_revision(1,99,2);  -- Will fail if this revision doesn't exist, or a later one does

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
SELECT new_wrms_revision(1,99,3, 'Pizza Bread' );

-- The actual changes for this revision
ALTER TABLE saved_queries ADD COLUMN public BOOLEAN ;
ALTER TABLE saved_queries ALTER COLUMN public SET DEFAULT FALSE;

ALTER TABLE saved_queries ADD COLUMN updated TIMESTAMP WITH TIME ZONE;
ALTER TABLE saved_queries ALTER COLUMN updated SET DEFAULT current_timestamp;

-- And finally commit that to make it a logical unit...
COMMIT;
