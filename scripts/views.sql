-- Views for WRMS

-- An insertable view that joins the organisation, work_system and usr tables
-- for the specific case of creating an organisation, general work_system and
-- primary user representative in one action.

DROP VIEW organisation_plus CASCADE;
CREATE OR REPLACE VIEW organisation_plus AS
  SELECT organisation.org_code, organisation.active AS org_active, debtor_no, work_rate,
      abbreviation, org_name, current_sla, admin_user_no, general_system,
      work_system.system_id, work_system.system_code, work_system.active AS system_active, system_desc, organisation_specific,
      usr.user_no, status, email_ok, joined, last_update,
      username, password, fullname, email,
      system_usr.role
    FROM organisation
      LEFT JOIN work_system ON organisation.general_system = work_system.system_id
      LEFT JOIN usr ON organisation.admin_user_no = usr.user_no
      LEFT JOIN system_usr ON system_usr.user_no = usr.user_no AND system_usr.system_id = work_system.system_id;

CREATE or REPLACE RULE organisation_plus AS ON INSERT TO organisation_plus
DO INSTEAD
(
  INSERT INTO organisation ( org_code, active, debtor_no, work_rate, abbreviation, org_name, current_sla, admin_user_no, general_system )
    VALUES(
      COALESCE( NEW.org_code, nextval('organisation_org_code_seq')),
      COALESCE( NEW.org_active, TRUE),
      NEW.debtor_no, NEW.work_rate, NEW.abbreviation, NEW.org_name,
      COALESCE( NEW.current_sla, FALSE),
      COALESCE( NEW.admin_user_no, nextval('usr_user_no_seq')),
      COALESCE( NEW.general_system, nextval('work_system_system_id_seq'))
    );
  INSERT INTO work_system ( system_id, system_code, active, system_desc, organisation_specific )
    VALUES(
      COALESCE( NEW.system_id, currval('work_system_system_id_seq')),
      COALESCE( NEW.system_code, lower( NEW.abbreviation || '-GEN') ),
      COALESCE( NEW.system_active, TRUE),
      COALESCE( NEW.system_desc, NEW.org_name || ' - General Work' ),
      COALESCE( NEW.organisation_specific, TRUE)
    );
  INSERT INTO usr ( user_no, status, email_ok, joined, last_update, username, password, fullname, email )
    VALUES(
      COALESCE( NEW.user_no, currval('usr_user_no_seq')),
      COALESCE( NEW.status, 'A'),
      COALESCE( NEW.email_ok, TRUE ),
      COALESCE( NEW.joined, current_timestamp),
      COALESCE( NEW.last_update, current_timestamp),
      NEW.username, NEW.password, NEW.fullname, NEW.email
    );
  INSERT INTO system_usr ( user_no, role, system_id )
    VALUES(
      COALESCE( NEW.user_no, currval('usr_user_no_seq')),
      COALESCE( NEW.role, 'C' ),
      COALESCE( NEW.system_id, currval('work_system_system_id_seq'))
    );
  INSERT INTO org_system ( org_code, system_id )
    VALUES(
      COALESCE( NEW.org_code, currval('organisation_org_code_seq')),
      COALESCE( NEW.system_id, currval('work_system_system_id_seq'))
    );
);

GRANT SELECT, INSERT ON organisation_plus TO general;
