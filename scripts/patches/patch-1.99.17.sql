BEGIN;

SELECT check_wrms_revision(1,99,16);  -- Will fail if this revision doesn't exist, or a later one does
SELECT new_wrms_revision(1,99,17, 'Savoury Rice Muffins' );

ALTER TABLE work_system DROP CONSTRAINT work_system_pkey;
ALTER TABLE work_system ADD CONSTRAINT work_system_pkey PRIMARY KEY (system_id);

\set table request_history
\set field entered_by
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table request_history
\set field requester_id
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

DELETE FROM help_hit WHERE user_no = 0;
\set table help_hit
\set field user_no
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;

\set table infonode
\set field created_by
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

UPDATE organisation SET admin_user_no = NULL WHERE admin_user_no = 0;
\set table organisation
\set field admin_user_no
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table wu
\set field wu_by
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table wu_vote
\set field wu_by
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE RESTRICT ON UPDATE CASCADE;

\set table wu_vote
\set field vote_by
\set constraint :table _ :field _fkey
ALTER TABLE :table ADD CONSTRAINT :constraint FOREIGN KEY (:field) REFERENCES usr(user_no) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;
