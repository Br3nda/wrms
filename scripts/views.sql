-- Views for WRMS

-- An insertable / updateable view that joins the organisation, work_system and usr tables
-- for the specific case of the designated primary representative and general work system
-- for the organisation.

CREATE OR REPLACE VIEW organisation_plus AS
SELECT organisation.org_code, organisation.active, debtor_no, work_rate,
       abbreviation, current_sla, admin_user_no,
       work_system.system_code, work_system.active, system_desc, organisation_specific,
       usr.user_no, status, email_ok, joined, last_update, last_accessed,
       username, password, fullname, email,
FROM organisation
         LEFT JOIN work_system ON organisation.generic_system = work_system.system_code
         LEFT JOIN usr ON organisation.admin_user_no = usr.user_no

CREATE or REPLACE RULE organisation_plus AS ON UPDATE TO organisation_plus
DO INSTEAD
(
  UPDATE organisation
    SET
      org_code = NEW.org_code,
      active = NEW.active,
      debtor_no = NEW.debtor_no,
      work_rate = NEW.work_rate,
      abbreviation = NEW.abbreviation,
      current_sla = NEW.current_sla,
      org_name = NEW.org_name
    WHERE
      org_code = OLD.org_code;

  UPDATE usr
    SET
      org_code = NEW.org_code,
      user_no = NEW.user_no,
      active = NEW.active,
      email_ok = NEW.email_ok,
      joined = NEW.joined,
      updated = NEW.updated,
      last_used = NEW.last_used,
      username = NEW.username,
      password = NEW.password,
      fullname = NEW.fullname,
      email = NEW.email,
      config_data = NEW.config_data
    WHERE
      user_no = OLD.user_no;

  UPDATE work_system
    SET
      user_no = NEW.user_no,
      bank_no = NEW.bank_no,
      is_banker = NEW.is_banker,
      other_shite = NEW.other_shite
    WHERE
      user_no = OLD.user_no;

  UPDATE org_system
    SET
      org_code = NEW.org_code,
      system_code = NEW.system_code
    WHERE
      org_code = OLD.org_code AND system_code = OLD.system_code;

  UPDATE system_usr
    SET
      user_no = NEW.user_no,
      system_code = NEW.system_code
    WHERE
      system_code = OLD.system_code AND user_no = OLD.user_no;
);

CREATE or REPLACE RULE usr_client_insert AS ON INSERT TO usr_client
DO INSTEAD
(
  INSERT INTO usr ( user_no, active, email_ok, joined, updated, last_used, username, password, fullname, email, config_data, date_format_type )
  VALUES( COALESCE( NEW.user_no, nextval('usr_user_no_seq')),
  COALESCE( NEW.active, TRUE),
  NEW.email_ok,
  COALESCE( NEW.joined, current_timestamp),
  NEW.updated, NEW.last_used, NEW.username, NEW.password, NEW.fullname, NEW.email, NEW.config_data, NEW.date_format_type );
  INSERT INTO client ( user_no, bank_no, is_banker, other_shite )
  VALUES( COALESCE( NEW.user_no, currval('usr_user_no_seq')),
  NEW.bank_no, NEW.is_banker, NEW.other_shite );
);

GRANT SELECT, INSERT, UPDATE ON usr_client TO general;
