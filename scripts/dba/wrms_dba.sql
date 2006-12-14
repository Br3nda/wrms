--
-- PostgreSQL database dump
--

SET client_encoding = 'UNICODE';
SET check_function_bodies = false;

SET SESSION AUTHORIZATION 'general';

SET search_path = public, pg_catalog;

--
-- TOC entry 229 (OID 4843628)
-- Name: plpgsql_call_handler(); Type: FUNC PROCEDURAL LANGUAGE; Schema: public; Owner: general
--

CREATE FUNCTION plpgsql_call_handler() RETURNS language_handler
    AS '$libdir/plpgsql', 'plpgsql_call_handler'
    LANGUAGE c;


SET SESSION AUTHORIZATION DEFAULT;

--
-- TOC entry 224 (OID 4843629)
-- Name: plpgsql; Type: PROCEDURAL LANGUAGE; Schema: public; Owner: 
--

CREATE TRUSTED PROCEDURAL LANGUAGE plpgsql HANDLER plpgsql_call_handler;


SET SESSION AUTHORIZATION 'postgres';

--
-- TOC entry 4 (OID 2200)
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO PUBLIC;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 16 (OID 4843546)
-- Name: supported_locales; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE supported_locales (
    locale text NOT NULL,
    locale_name_en text,
    locale_name_locale text
);


--
-- TOC entry 17 (OID 4843546)
-- Name: supported_locales; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE supported_locales FROM PUBLIC;
GRANT SELECT ON TABLE supported_locales TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 18 (OID 4843555)
-- Name: usr; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE usr (
    user_no serial NOT NULL,
    active boolean DEFAULT true,
    joined timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    updated timestamp with time zone,
    last_used timestamp with time zone,
    username text NOT NULL,
    "password" text,
    fullname text,
    email text,
    config_data text,
    date_format_type text DEFAULT 'E'::text,
    locale text,
    org_code integer,
    last_update timestamp with time zone,
    "location" text,
    mobile text,
    phone text,
    email_ok boolean,
    base_rate numeric
);


--
-- TOC entry 19 (OID 4843555)
-- Name: usr; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE usr FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE usr TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 144 (OID 4843555)
-- Name: usr_user_no_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE usr_user_no_seq FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE usr_user_no_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 225 (OID 4843566)
-- Name: max_usr(); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION max_usr() RETURNS integer
    AS 'SELECT max(user_no) FROM usr'
    LANGUAGE sql;


--
-- TOC entry 20 (OID 4843568)
-- Name: usr_setting; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE usr_setting (
    user_no integer NOT NULL,
    setting_name text NOT NULL,
    setting_value text
);


--
-- TOC entry 21 (OID 4843568)
-- Name: usr_setting; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE usr_setting FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE usr_setting TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 226 (OID 4843579)
-- Name: get_usr_setting(integer, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION get_usr_setting(integer, text) RETURNS text
    AS 'SELECT setting_value FROM usr_setting
            WHERE usr_setting.user_no = $1
            AND usr_setting.setting_name = $2 '
    LANGUAGE sql;


--
-- TOC entry 22 (OID 4843582)
-- Name: roles; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE roles (
    role_no serial NOT NULL,
    role_name text
);


--
-- TOC entry 23 (OID 4843582)
-- Name: roles; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE roles FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE roles TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 227 (OID 4843590)
-- Name: max_roles(); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION max_roles() RETURNS integer
    AS 'SELECT max(role_no) FROM roles'
    LANGUAGE sql;


--
-- TOC entry 24 (OID 4843591)
-- Name: role_member; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE role_member (
    role_no integer,
    user_no integer
);


--
-- TOC entry 25 (OID 4843591)
-- Name: role_member; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE role_member FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE role_member TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 26 (OID 4843603)
-- Name: session; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE "session" (
    session_id serial NOT NULL,
    user_no integer,
    session_start timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    session_end timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    session_key text,
    session_config text
);


--
-- TOC entry 27 (OID 4843603)
-- Name: session; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE "session" FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE "session" TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 145 (OID 4843603)
-- Name: session_session_id_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE session_session_id_seq FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE session_session_id_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 228 (OID 4843617)
-- Name: max_session(); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION max_session() RETURNS integer
    AS 'SELECT max(session_id) FROM session'
    LANGUAGE sql;


--
-- TOC entry 28 (OID 4843618)
-- Name: tmp_password; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE tmp_password (
    user_no integer,
    "password" text,
    valid_until timestamp with time zone DEFAULT (('now'::text)::timestamp(6) with time zone + '1 day'::interval)
);


--
-- TOC entry 29 (OID 4843618)
-- Name: tmp_password; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE tmp_password FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE tmp_password TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 30 (OID 4843630)
-- Name: awl_db_revision; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE awl_db_revision (
    schema_id integer,
    schema_major integer,
    schema_minor integer,
    schema_patch integer,
    schema_name text,
    applied_on timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone
);


--
-- TOC entry 31 (OID 4843630)
-- Name: awl_db_revision; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE awl_db_revision FROM PUBLIC;
GRANT SELECT ON TABLE awl_db_revision TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 230 (OID 4843636)
-- Name: check_db_revision(integer, integer, integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION check_db_revision(integer, integer, integer) RETURNS boolean
    AS '
   DECLARE
      major ALIAS FOR $1;
      minor ALIAS FOR $2;
      patch ALIAS FOR $3;
      matching INT;
   BEGIN
      SELECT COUNT(*) INTO matching FROM awl_db_revision
                      WHERE schema_major = major AND schema_minor = minor AND schema_patch = patch;
      IF matching != 1 THEN
        RAISE EXCEPTION ''Database has not been upgraded to %.%.%'', major, minor, patch;
        RETURN FALSE;
      END IF;
      SELECT COUNT(*) INTO matching FROM awl_db_revision
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 231 (OID 4843637)
-- Name: new_db_revision(integer, integer, integer, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION new_db_revision(integer, integer, integer, text) RETURNS boolean
    AS '
   DECLARE
      major ALIAS FOR $1;
      minor ALIAS FOR $2;
      patch ALIAS FOR $3;
      blurb ALIAS FOR $4;
      new_id INT;
   BEGIN
      SELECT MAX(schema_id) + 1 INTO new_id FROM awl_db_revision;
      IF NOT FOUND OR new_id IS NULL THEN
        new_id := 1;
      END IF;
      INSERT INTO awl_db_revision (schema_id, schema_major, schema_minor, schema_patch, schema_name)
                    VALUES( new_id, major, minor, patch, blurb );
      RETURN TRUE;
   END;
'
    LANGUAGE plpgsql;


--
-- TOC entry 32 (OID 4843641)
-- Name: organisation; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE organisation (
    org_code serial NOT NULL,
    active boolean DEFAULT true,
    debtor_no integer,
    work_rate double precision,
    admin_user_no integer,
    abbreviation text,
    current_sla boolean,
    org_name text,
    general_system integer
);


--
-- TOC entry 33 (OID 4843641)
-- Name: organisation; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE organisation FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE organisation TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 146 (OID 4843641)
-- Name: organisation_org_code_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE organisation_org_code_seq FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE organisation_org_code_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 232 (OID 4843650)
-- Name: max_organisation(); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION max_organisation() RETURNS integer
    AS 'SELECT max(org_code) FROM organisation'
    LANGUAGE sql;


--
-- TOC entry 34 (OID 4843657)
-- Name: request; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request (
    request_id serial NOT NULL,
    request_on timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    active boolean DEFAULT true,
    last_status character(1) DEFAULT 'N'::bpchar,
    wap_status smallint DEFAULT 0,
    sla_response_hours smallint DEFAULT 0,
    urgency smallint,
    importance smallint,
    severity_code smallint,
    request_type smallint,
    requester_id integer,
    eta timestamp without time zone,
    last_activity timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    sla_response_time interval DEFAULT '00:00:00'::interval,
    sla_response_type character(1) DEFAULT 'O'::bpchar,
    requested_by_date timestamp without time zone,
    agreed_due_date timestamp without time zone,
    request_by text,
    brief text,
    detailed text,
    entered_by integer,
    system_id integer NOT NULL,
    parent_request integer
);


--
-- TOC entry 35 (OID 4843657)
-- Name: request; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE request TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 147 (OID 4843657)
-- Name: request_request_id_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_request_id_seq FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE request_request_id_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 36 (OID 4843678)
-- Name: work_system; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE work_system (
    system_id integer DEFAULT nextval('work_system_system_id_seq'::text) NOT NULL,
    organisation_specific boolean DEFAULT false,
    system_code text NOT NULL,
    system_desc text,
    active boolean
);


--
-- TOC entry 37 (OID 4843678)
-- Name: work_system; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE work_system FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE work_system TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 38 (OID 4843685)
-- Name: org_system; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE org_system (
    org_code integer NOT NULL,
    system_id integer NOT NULL
);


--
-- TOC entry 39 (OID 4843685)
-- Name: org_system; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE org_system FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE org_system TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 40 (OID 4843692)
-- Name: request_status; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_status (
    request_id integer NOT NULL,
    status_on timestamp without time zone NOT NULL,
    status_by_id integer,
    status_by text,
    status_code text
);


--
-- TOC entry 41 (OID 4843692)
-- Name: request_status; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_status FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE request_status TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 42 (OID 4843700)
-- Name: request_quote; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_quote (
    quote_id serial NOT NULL,
    request_id integer,
    quoted_on timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    quote_amount double precision,
    quote_by_id integer,
    quoted_by text,
    quote_type text,
    quote_units text,
    quote_brief text,
    quote_details text,
    approved_by_id integer,
    approved_on timestamp without time zone,
    invoice_no integer
);


--
-- TOC entry 43 (OID 4843700)
-- Name: request_quote; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_quote FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE request_quote TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 148 (OID 4843700)
-- Name: request_quote_quote_id_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_quote_quote_id_seq FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE request_quote_quote_id_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 233 (OID 4843710)
-- Name: max_quote(); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION max_quote() RETURNS integer
    AS 'SELECT max(quote_id) FROM request_quote'
    LANGUAGE sql;


--
-- TOC entry 44 (OID 4843711)
-- Name: request_allocated; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_allocated (
    request_id integer NOT NULL,
    allocated_on timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    allocated_to_id integer NOT NULL,
    allocated_to text
);


--
-- TOC entry 45 (OID 4843711)
-- Name: request_allocated; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_allocated FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE request_allocated TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 46 (OID 4843719)
-- Name: request_timesheet; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_timesheet (
    timesheet_id serial NOT NULL,
    request_id integer,
    work_on timestamp without time zone,
    ok_to_charge boolean DEFAULT false,
    work_quantity double precision,
    work_duration interval,
    work_by_id integer,
    work_by text,
    work_description text,
    work_rate double precision,
    work_charged timestamp without time zone,
    charged_amount double precision,
    charged_by_id integer,
    work_units text,
    charged_details text,
    entry_details text,
    dav_etag text
);


--
-- TOC entry 47 (OID 4843719)
-- Name: request_timesheet; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_timesheet FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE request_timesheet TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 149 (OID 4843719)
-- Name: request_timesheet_timesheet_id_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_timesheet_timesheet_id_seq FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE request_timesheet_timesheet_id_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 234 (OID 4843729)
-- Name: max_timesheet(); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION max_timesheet() RETURNS integer
    AS 'SELECT max(timesheet_id) FROM request_timesheet'
    LANGUAGE sql;


--
-- TOC entry 48 (OID 4843730)
-- Name: timesheet_note; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE timesheet_note (
    note_date timestamp without time zone NOT NULL,
    note_by_id integer NOT NULL,
    note_detail text
);


--
-- TOC entry 49 (OID 4843730)
-- Name: timesheet_note; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE timesheet_note FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE timesheet_note TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 50 (OID 4843737)
-- Name: request_note; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_note (
    request_id integer NOT NULL,
    note_on timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL,
    note_by_id integer,
    note_by text,
    note_detail text
);


--
-- TOC entry 51 (OID 4843737)
-- Name: request_note; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_note FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE request_note TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 255 (OID 4843745)
-- Name: get_last_note_on(integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION get_last_note_on(integer) RETURNS timestamp without time zone
    AS 'SELECT max(note_on)::timestamp FROM request_note WHERE request_note.request_id = $1
    '
    LANGUAGE sql;


--
-- TOC entry 52 (OID 4843746)
-- Name: request_interested; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_interested (
    request_id integer NOT NULL,
    user_no integer DEFAULT -1 NOT NULL,
    username text NOT NULL
);


--
-- TOC entry 53 (OID 4843746)
-- Name: request_interested; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_interested FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE request_interested TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 54 (OID 4843754)
-- Name: request_request; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_request (
    request_id integer NOT NULL,
    to_request_id integer NOT NULL,
    link_type character(1) NOT NULL,
    link_data text
);


--
-- TOC entry 55 (OID 4843754)
-- Name: request_request; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_request FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE request_request TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 56 (OID 4843762)
-- Name: request_history; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_history (
    request_id integer,
    request_on timestamp without time zone,
    active boolean,
    last_status character(1),
    wap_status smallint,
    sla_response_hours smallint,
    urgency smallint,
    importance smallint,
    severity_code smallint,
    request_type smallint,
    requester_id integer,
    eta timestamp without time zone,
    last_activity timestamp without time zone,
    sla_response_time interval,
    sla_response_type character(1),
    requested_by_date timestamp without time zone,
    agreed_due_date timestamp without time zone,
    request_by text,
    brief text,
    detailed text,
    system_code text,
    entered_by integer,
    modified_on timestamp(6) with time zone DEFAULT ('now'::text)::timestamp(6) with time zone
);


--
-- TOC entry 57 (OID 4843762)
-- Name: request_history; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_history FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE request_history TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 58 (OID 4843771)
-- Name: request_attachment; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_attachment (
    attachment_id serial NOT NULL,
    request_id integer,
    attached_on timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    attached_by integer,
    att_brief text,
    att_description text,
    att_filename text,
    att_type text,
    att_inline boolean DEFAULT false,
    att_width integer,
    att_height integer
);


--
-- TOC entry 59 (OID 4843771)
-- Name: request_attachment; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_attachment FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE request_attachment TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 150 (OID 4843771)
-- Name: request_attachment_attachment_id_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_attachment_attachment_id_seq FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE request_attachment_attachment_id_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 235 (OID 4843782)
-- Name: max_attachment(); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION max_attachment() RETURNS integer
    AS 'SELECT max(attachment_id) FROM request_attachment'
    LANGUAGE sql;


--
-- TOC entry 60 (OID 4843783)
-- Name: lookup_code; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE lookup_code (
    source_table text,
    source_field text,
    lookup_seq smallint DEFAULT 0,
    lookup_code text,
    lookup_desc text,
    lookup_misc text
);


--
-- TOC entry 61 (OID 4843783)
-- Name: lookup_code; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE lookup_code FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE lookup_code TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 256 (OID 4843791)
-- Name: get_lookup_desc(text, text, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION get_lookup_desc(text, text, text) RETURNS text
    AS 'SELECT lookup_desc AS RESULT FROM lookup_code
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;'
    LANGUAGE sql;


--
-- TOC entry 244 (OID 4843792)
-- Name: get_lookup_misc(text, text, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION get_lookup_misc(text, text, text) RETURNS text
    AS 'SELECT lookup_misc AS RESULT FROM lookup_code
               WHERE source_table = $1 AND source_field = $2 AND lookup_code = $3;'
    LANGUAGE sql;


--
-- TOC entry 245 (OID 4843793)
-- Name: get_status_desc(character); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION get_status_desc(character) RETURNS text
    AS 'SELECT lookup_desc AS status_desc FROM lookup_code
            WHERE source_table=''request'' AND source_field=''status_code''
            AND lower(lookup_code) = lower($1)
    '
    LANGUAGE sql;


--
-- TOC entry 62 (OID 4843794)
-- Name: attachment_type; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE attachment_type (
    type_code text NOT NULL,
    type_desc text,
    seq integer,
    mime_type text,
    pattern text,
    mime_pattern text
);


--
-- TOC entry 63 (OID 4843794)
-- Name: attachment_type; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE attachment_type FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE attachment_type TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 64 (OID 4843811)
-- Name: ugroup; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE ugroup (
    group_no integer DEFAULT nextval('public.ugroup_group_no_seq'::text) NOT NULL,
    module_name text,
    group_name text,
    seq integer
);


--
-- TOC entry 65 (OID 4843811)
-- Name: ugroup; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE ugroup FROM PUBLIC;
GRANT SELECT ON TABLE ugroup TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 236 (OID 4843817)
-- Name: max_group(); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION max_group() RETURNS integer
    AS 'SELECT max(group_no) FROM ugroup'
    LANGUAGE sql;


--
-- TOC entry 66 (OID 4843820)
-- Name: system_usr; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE system_usr (
    user_no integer NOT NULL,
    system_id integer NOT NULL,
    role character(1) NOT NULL
);


--
-- TOC entry 67 (OID 4843820)
-- Name: system_usr; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE system_usr FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE system_usr TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 68 (OID 4843824)
-- Name: saved_queries; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE saved_queries (
    user_no integer NOT NULL,
    query_name text NOT NULL,
    query_type text,
    query_sql text,
    query_params text,
    maxresults integer,
    rlsort text,
    rlseq text,
    public boolean DEFAULT false,
    updated timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    in_menu boolean DEFAULT false
);


--
-- TOC entry 69 (OID 4843824)
-- Name: saved_queries; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE saved_queries FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE saved_queries TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 70 (OID 4843831)
-- Name: help_hit; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE help_hit (
    user_no integer NOT NULL,
    topic text NOT NULL,
    times integer,
    "last" timestamp without time zone
);


--
-- TOC entry 71 (OID 4843831)
-- Name: help_hit; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE help_hit FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE help_hit TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 72 (OID 4843838)
-- Name: help; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE help (
    topic text NOT NULL,
    seq integer NOT NULL,
    title text,
    content text
);


--
-- TOC entry 73 (OID 4843838)
-- Name: help; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE help FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE help TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 74 (OID 4843847)
-- Name: infonode; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE infonode (
    node_id serial NOT NULL,
    nodename text,
    created_on timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    created_by integer,
    node_type integer DEFAULT 0
);


--
-- TOC entry 75 (OID 4843847)
-- Name: infonode; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE infonode FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE infonode TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 151 (OID 4843847)
-- Name: infonode_node_id_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE infonode_node_id_seq FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE infonode_node_id_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 76 (OID 4843859)
-- Name: wu; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE wu (
    node_id integer NOT NULL,
    wu_by integer NOT NULL,
    wu_on timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone,
    votes_plus integer DEFAULT 0,
    votes_minus integer DEFAULT 0,
    flags text DEFAULT ''::text,
    content text
);


--
-- TOC entry 77 (OID 4843859)
-- Name: wu; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE wu FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE wu TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 78 (OID 4843872)
-- Name: wu_vote; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE wu_vote (
    node_id integer NOT NULL,
    wu_by integer NOT NULL,
    vote_by integer NOT NULL,
    vote_amount integer,
    flag character(1),
    vote_on timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone
);


--
-- TOC entry 79 (OID 4843872)
-- Name: wu_vote; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE wu_vote FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE wu_vote TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 80 (OID 4843877)
-- Name: nodetrack; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE nodetrack (
    node_from integer NOT NULL,
    node_to integer NOT NULL,
    no_times integer
);


--
-- TOC entry 81 (OID 4843877)
-- Name: nodetrack; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE nodetrack FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE ON TABLE nodetrack TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 82 (OID 4843884)
-- Name: organisation_tag; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE organisation_tag (
    tag_id serial NOT NULL,
    org_code integer,
    tag_description text,
    tag_sequence integer DEFAULT 0,
    active boolean DEFAULT true
);


--
-- TOC entry 83 (OID 4843884)
-- Name: organisation_tag; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE organisation_tag FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE organisation_tag TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 152 (OID 4843884)
-- Name: organisation_tag_tag_id_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE organisation_tag_tag_id_seq FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE organisation_tag_tag_id_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 84 (OID 4843899)
-- Name: request_tag; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_tag (
    request_id integer NOT NULL,
    tag_id integer NOT NULL,
    tagged_on timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone
);


--
-- TOC entry 85 (OID 4843899)
-- Name: request_tag; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_tag FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE request_tag TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 86 (OID 4843913)
-- Name: wrms_revision; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE wrms_revision (
    schema_id integer,
    schema_major integer,
    schema_minor integer,
    schema_patch integer,
    schema_name text,
    applied_on timestamp with time zone DEFAULT ('now'::text)::timestamp(6) with time zone
);


--
-- TOC entry 87 (OID 4843913)
-- Name: wrms_revision; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE wrms_revision FROM PUBLIC;
GRANT SELECT ON TABLE wrms_revision TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 88 (OID 4843921)
-- Name: organisation_action; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE organisation_action (
    action_id serial NOT NULL,
    org_code integer,
    action_description text,
    action_sequence integer DEFAULT 0,
    active boolean DEFAULT true
);


--
-- TOC entry 89 (OID 4843921)
-- Name: organisation_action; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE organisation_action FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE organisation_action TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 5 (OID 4843929)
-- Name: seq_qa_approval_id; Type: SEQUENCE; Schema: public; Owner: general
--

CREATE SEQUENCE seq_qa_approval_id
    START WITH 1
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 6 (OID 4843931)
-- Name: seq_qa_approval_type_id; Type: SEQUENCE; Schema: public; Owner: general
--

CREATE SEQUENCE seq_qa_approval_type_id
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 7 (OID 4843931)
-- Name: seq_qa_approval_type_id; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE seq_qa_approval_type_id FROM PUBLIC;
GRANT SELECT,UPDATE ON TABLE seq_qa_approval_type_id TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 8 (OID 4843933)
-- Name: seq_qa_document_id; Type: SEQUENCE; Schema: public; Owner: general
--

CREATE SEQUENCE seq_qa_document_id
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 9 (OID 4843933)
-- Name: seq_qa_document_id; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE seq_qa_document_id FROM PUBLIC;
GRANT SELECT,UPDATE ON TABLE seq_qa_document_id TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 10 (OID 4843935)
-- Name: seq_qa_model_id; Type: SEQUENCE; Schema: public; Owner: general
--

CREATE SEQUENCE seq_qa_model_id
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 11 (OID 4843935)
-- Name: seq_qa_model_id; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE seq_qa_model_id FROM PUBLIC;
GRANT SELECT,UPDATE ON TABLE seq_qa_model_id TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 12 (OID 4843937)
-- Name: seq_qa_step_id; Type: SEQUENCE; Schema: public; Owner: general
--

CREATE SEQUENCE seq_qa_step_id
    INCREMENT BY 1
    MAXVALUE 2147483647
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 13 (OID 4843937)
-- Name: seq_qa_step_id; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE seq_qa_step_id FROM PUBLIC;
GRANT SELECT,UPDATE ON TABLE seq_qa_step_id TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 90 (OID 4843939)
-- Name: qa_approval; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_approval (
    qa_step_id integer NOT NULL,
    qa_approval_type_id integer NOT NULL,
    qa_approval_order integer DEFAULT 0 NOT NULL
);


--
-- TOC entry 92 (OID 4843939)
-- Name: qa_approval; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_approval FROM PUBLIC;
GRANT SELECT ON TABLE qa_approval TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 93 (OID 4843944)
-- Name: qa_approval_type; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_approval_type (
    qa_approval_type_id integer NOT NULL,
    qa_approval_type_desc text NOT NULL
);


--
-- TOC entry 95 (OID 4843944)
-- Name: qa_approval_type; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_approval_type FROM PUBLIC;
GRANT SELECT ON TABLE qa_approval_type TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 96 (OID 4843951)
-- Name: qa_document; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_document (
    qa_document_id integer NOT NULL,
    qa_document_title text NOT NULL,
    qa_document_desc text
);


--
-- TOC entry 97 (OID 4843951)
-- Name: qa_document; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_document FROM PUBLIC;
GRANT SELECT ON TABLE qa_document TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 98 (OID 4843958)
-- Name: qa_model; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_model (
    qa_model_id integer NOT NULL,
    qa_model_name text NOT NULL,
    qa_model_desc text,
    qa_model_order integer DEFAULT 0 NOT NULL
);


--
-- TOC entry 100 (OID 4843958)
-- Name: qa_model; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_model FROM PUBLIC;
GRANT SELECT ON TABLE qa_model TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 101 (OID 4843966)
-- Name: qa_model_documents; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_model_documents (
    qa_model_id integer NOT NULL,
    qa_document_id integer NOT NULL,
    path_to_template text,
    path_to_example text
);


--
-- TOC entry 102 (OID 4843966)
-- Name: qa_model_documents; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_model_documents FROM PUBLIC;
GRANT SELECT ON TABLE qa_model_documents TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 103 (OID 4843973)
-- Name: qa_model_step; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_model_step (
    qa_model_id integer NOT NULL,
    qa_step_id integer NOT NULL
);


--
-- TOC entry 104 (OID 4843973)
-- Name: qa_model_step; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_model_step FROM PUBLIC;
GRANT SELECT ON TABLE qa_model_step TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 105 (OID 4843977)
-- Name: qa_phase; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_phase (
    qa_phase text NOT NULL,
    qa_phase_desc text,
    qa_phase_order integer DEFAULT 0 NOT NULL
);


--
-- TOC entry 107 (OID 4843977)
-- Name: qa_phase; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_phase FROM PUBLIC;
GRANT SELECT ON TABLE qa_phase TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 108 (OID 4843985)
-- Name: qa_project_approval; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_project_approval (
    qa_approval_id integer NOT NULL,
    project_id integer NOT NULL,
    qa_step_id integer NOT NULL,
    qa_approval_type_id integer NOT NULL,
    approval_status text,
    assigned_to_usr integer,
    assigned_datetime timestamp without time zone,
    approval_by_usr integer,
    approval_datetime timestamp without time zone,
    "comment" text,
    CONSTRAINT ckc_approval_status_qa_proje CHECK (((approval_status IS NULL) OR ((((approval_status = 'p'::text) OR (approval_status = 'y'::text)) OR (approval_status = 'n'::text)) OR (approval_status = 's'::text))))
);


--
-- TOC entry 114 (OID 4843985)
-- Name: qa_project_approval; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_project_approval FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE qa_project_approval TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 115 (OID 4843993)
-- Name: qa_project_step; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_project_step (
    project_id integer NOT NULL,
    qa_step_id integer NOT NULL,
    request_id integer NOT NULL,
    responsible_usr integer,
    responsible_datetime timestamp without time zone,
    notes text
);


--
-- TOC entry 122 (OID 4843993)
-- Name: qa_project_step; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_project_step FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE qa_project_step TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 123 (OID 4844000)
-- Name: qa_project_step_approval; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_project_step_approval (
    project_id integer NOT NULL,
    qa_step_id integer NOT NULL,
    qa_approval_type_id integer NOT NULL,
    last_approval_status text,
    CONSTRAINT ckc_last_approval_sta_qa_proje CHECK (((last_approval_status IS NULL) OR ((((last_approval_status = 'p'::text) OR (last_approval_status = 'y'::text)) OR (last_approval_status = 'n'::text)) OR (last_approval_status = 's'::text))))
);


--
-- TOC entry 127 (OID 4844000)
-- Name: qa_project_step_approval; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_project_step_approval FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE qa_project_step_approval TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 128 (OID 4844008)
-- Name: qa_step; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE qa_step (
    qa_step_id integer NOT NULL,
    qa_phase text NOT NULL,
    qa_document_id integer,
    qa_step_desc text,
    qa_step_notes text,
    qa_step_order integer DEFAULT 0 NOT NULL,
    mandatory boolean DEFAULT false NOT NULL,
    enabled boolean DEFAULT true NOT NULL
);


--
-- TOC entry 130 (OID 4844008)
-- Name: qa_step; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE qa_step FROM PUBLIC;
GRANT SELECT ON TABLE qa_step TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 131 (OID 4844018)
-- Name: request_project; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_project (
    request_id integer NOT NULL,
    project_manager integer,
    qa_mentor integer,
    qa_model_id integer,
    qa_phase text
);


--
-- TOC entry 137 (OID 4844018)
-- Name: request_project; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_project FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE request_project TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 238 (OID 4844117)
-- Name: set_interested(integer, integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION set_interested(integer, integer) RETURNS integer
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 239 (OID 4844118)
-- Name: set_allocated(integer, integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION set_allocated(integer, integer) RETURNS integer
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 240 (OID 4844119)
-- Name: set_system_role(integer, integer, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION set_system_role(integer, integer, text) RETURNS integer
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 241 (OID 4844120)
-- Name: column_type(text, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION column_type(text, text) RETURNS text
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 242 (OID 4844121)
-- Name: last_status_on(integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION last_status_on(integer) RETURNS timestamp without time zone
    AS '
  DECLARE
    res TIMESTAMP;
  BEGIN
    SELECT status_on INTO res
          FROM request_status
          WHERE request_status.request_id = $1
            ORDER BY status_on DESC LIMIT 1;
    RETURN res;
  END;
'
    LANGUAGE plpgsql;


--
-- TOC entry 243 (OID 4844122)
-- Name: help_hit(integer, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION help_hit(integer, text) RETURNS integer
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 246 (OID 4844123)
-- Name: cast_vote(integer, integer, integer, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION cast_vote(integer, integer, integer, text) RETURNS integer
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 247 (OID 4844124)
-- Name: user_votes(integer, integer, integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION user_votes(integer, integer, integer) RETURNS integer
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 248 (OID 4844125)
-- Name: last_org_request(integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION last_org_request(integer) RETURNS timestamp without time zone
    AS '
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
'
    LANGUAGE plpgsql STABLE;


--
-- TOC entry 249 (OID 4844126)
-- Name: active_org_requests(integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION active_org_requests(integer) RETURNS integer
    AS '
  SELECT count(request_id)::int4 FROM request, usr
    WHERE usr.org_code = $1
      AND request.requester_id = usr.user_no
      AND request.active
      AND last_status NOT IN (''F'', ''C'');
'
    LANGUAGE sql STABLE;


--
-- TOC entry 250 (OID 4844127)
-- Name: set_request_status(integer, integer, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION set_request_status(integer, integer, text) RETURNS text
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 251 (OID 4844128)
-- Name: set_request_status(integer, integer, text, boolean); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION set_request_status(integer, integer, text, boolean) RETURNS text
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 259 (OID 4844129)
-- Name: request_tags(integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION request_tags(integer) RETURNS text
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 252 (OID 4844130)
-- Name: active_request(integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION active_request(integer) RETURNS boolean
    AS 'SELECT active FROM request WHERE request.request_id = $1'
    LANGUAGE sql;


--
-- TOC entry 253 (OID 4844131)
-- Name: max_request(); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION max_request() RETURNS integer
    AS 'SELECT max(request_id) FROM request'
    LANGUAGE sql;


--
-- TOC entry 254 (OID 4844132)
-- Name: request_sla_code(interval, character); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION request_sla_code(interval, character) RETURNS text
    AS 'SELECT text( date_part( ''hour'', $1) ) || ''|'' || text(CASE WHEN $2 ='' '' THEN ''O'' ELSE $2 END)
    '
    LANGUAGE sql;


--
-- TOC entry 257 (OID 4844133)
-- Name: check_wrms_revision(integer, integer, integer); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION check_wrms_revision(integer, integer, integer) RETURNS boolean
    AS '
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
'
    LANGUAGE plpgsql STABLE;


--
-- TOC entry 258 (OID 4844134)
-- Name: new_wrms_revision(integer, integer, integer, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION new_wrms_revision(integer, integer, integer, text) RETURNS boolean
    AS '
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
'
    LANGUAGE plpgsql;


--
-- TOC entry 138 (OID 4844365)
-- Name: group_member; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE group_member (
    group_no integer,
    user_no integer
);


--
-- TOC entry 139 (OID 4844365)
-- Name: group_member; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE group_member FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE group_member TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 237 (OID 4844380)
-- Name: set_system_role(integer, text, text); Type: FUNCTION; Schema: public; Owner: general
--

CREATE FUNCTION set_system_role(integer, text, text) RETURNS integer
    AS '
   DECLARE
      u_no ALIAS FOR $1;
      sys_code ALIAS FOR $2;
      new_role ALIAS FOR $3;
      curr_val TEXT;
   BEGIN
      SELECT role INTO curr_val FROM system_usr
                      WHERE user_no = u_no AND system_code = sys_code;
      IF FOUND THEN
        IF curr_val = new_role THEN
          RETURN u_no;
        ELSE
          UPDATE system_usr SET role = new_role
                      WHERE user_no = u_no AND system_code = sys_code;
        END IF;
      ELSE
        INSERT INTO system_usr (user_no, system_code, role)
                         VALUES( u_no, sys_code, new_role );
      END IF;
      RETURN u_no;
   END;
'
    LANGUAGE plpgsql;


--
-- TOC entry 140 (OID 4844464)
-- Name: request_qa_action; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE request_qa_action (
    request_id integer NOT NULL,
    action_on timestamp without time zone DEFAULT ('now'::text)::timestamp(6) with time zone NOT NULL,
    action_by integer,
    action_detail text
);


--
-- TOC entry 141 (OID 4844464)
-- Name: request_qa_action; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE request_qa_action FROM PUBLIC;
GRANT INSERT,SELECT ON TABLE request_qa_action TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 14 (OID 4844504)
-- Name: work_system_system_id_seq; Type: SEQUENCE; Schema: public; Owner: general
--

CREATE SEQUENCE work_system_system_id_seq
    INCREMENT BY 1
    NO MAXVALUE
    NO MINVALUE
    CACHE 1;


--
-- TOC entry 15 (OID 4844504)
-- Name: work_system_system_id_seq; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE work_system_system_id_seq FROM PUBLIC;
GRANT SELECT,UPDATE ON TABLE work_system_system_id_seq TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 142 (OID 4844556)
-- Name: caldav_data; Type: TABLE; Schema: public; Owner: general
--

CREATE TABLE caldav_data (
    user_no integer NOT NULL,
    dav_name text NOT NULL,
    dav_etag text,
    caldav_data text,
    caldav_type text,
    logged_user integer
);


--
-- TOC entry 143 (OID 4844556)
-- Name: caldav_data; Type: ACL; Schema: public; Owner: general
--

REVOKE ALL ON TABLE caldav_data FROM PUBLIC;
GRANT INSERT,SELECT,UPDATE,DELETE ON TABLE caldav_data TO general;


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 156 (OID 4843567)
-- Name: usr_sk1_unique_username; Type: INDEX; Schema: public; Owner: general
--

CREATE UNIQUE INDEX usr_sk1_unique_username ON usr USING btree (lower(username));


--
-- TOC entry 176 (OID 4843727)
-- Name: request_timesheet_skey1; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_timesheet_skey1 ON request_timesheet USING btree (work_on, work_by_id, request_id);


--
-- TOC entry 177 (OID 4843728)
-- Name: request_timesheet_skey2; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_timesheet_skey2 ON request_timesheet USING btree (ok_to_charge, request_id);


--
-- TOC entry 182 (OID 4843761)
-- Name: request_request_sk1; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_request_sk1 ON request_request USING btree (to_request_id);

ALTER TABLE request_request CLUSTER ON request_request_sk1;


--
-- TOC entry 183 (OID 4843768)
-- Name: xpk_request_history; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX xpk_request_history ON request_history USING btree (request_id, modified_on);


--
-- TOC entry 187 (OID 4843789)
-- Name: lookup_code_key; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX lookup_code_key ON lookup_code USING btree (source_table, source_field, lookup_seq, lookup_code);


--
-- TOC entry 186 (OID 4843790)
-- Name: lookup_code_ak1; Type: INDEX; Schema: public; Owner: general
--

CREATE UNIQUE INDEX lookup_code_ak1 ON lookup_code USING btree (source_table, source_field, lookup_code);


--
-- TOC entry 196 (OID 4843857)
-- Name: infonode_skey1; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX infonode_skey1 ON infonode USING btree (created_by, created_on);


--
-- TOC entry 197 (OID 4843858)
-- Name: infonode_skey2; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX infonode_skey2 ON infonode USING btree (created_on);


--
-- TOC entry 199 (OID 4843870)
-- Name: wu_skey1; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX wu_skey1 ON wu USING btree (wu_by, wu_on);


--
-- TOC entry 200 (OID 4843871)
-- Name: wu_skey2; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX wu_skey2 ON wu USING btree (wu_on);


--
-- TOC entry 203 (OID 4843881)
-- Name: nodetrack_skey1; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX nodetrack_skey1 ON nodetrack USING btree (node_from, node_to);


--
-- TOC entry 205 (OID 4843898)
-- Name: organisation_tag_sk1; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX organisation_tag_sk1 ON organisation_tag USING btree (tag_sequence, lower(tag_description));


--
-- TOC entry 207 (OID 4843912)
-- Name: request_tag_sk1; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_tag_sk1 ON request_tag USING btree (tag_id);


--
-- TOC entry 208 (OID 4844304)
-- Name: organisation_action_sk1; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX organisation_action_sk1 ON organisation_action USING btree (org_code, action_sequence, lower(action_description));


--
-- TOC entry 185 (OID 4844417)
-- Name: request_attachment_skey; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_attachment_skey ON request_attachment USING btree (request_id, attachment_id);


--
-- TOC entry 171 (OID 4844428)
-- Name: request_quote_skey; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_quote_skey ON request_quote USING btree (request_id, quote_id);


--
-- TOC entry 175 (OID 4844450)
-- Name: request_timesheet_req; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_timesheet_req ON request_timesheet USING btree (request_id, timesheet_id);

ALTER TABLE request_timesheet CLUSTER ON request_timesheet_req;


--
-- TOC entry 162 (OID 4844491)
-- Name: request_sk0; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_sk0 ON request USING btree (request_id) WHERE active;


--
-- TOC entry 163 (OID 4844492)
-- Name: request_sk1; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_sk1 ON request USING btree (requester_id) WHERE active;


--
-- TOC entry 165 (OID 4844493)
-- Name: request_sk3; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_sk3 ON request USING btree (last_status) WHERE active;


--
-- TOC entry 154 (OID 4844494)
-- Name: usr_pk2; Type: INDEX; Schema: public; Owner: general
--

CREATE UNIQUE INDEX usr_pk2 ON usr USING btree (lower(username));


--
-- TOC entry 166 (OID 4844507)
-- Name: work_system_pk1; Type: INDEX; Schema: public; Owner: general
--

CREATE UNIQUE INDEX work_system_pk1 ON work_system USING btree (system_id);


--
-- TOC entry 168 (OID 4844509)
-- Name: org_system_fk1; Type: INDEX; Schema: public; Owner: general
--

CREATE UNIQUE INDEX org_system_fk1 ON org_system USING btree (system_id, org_code);


--
-- TOC entry 191 (OID 4844514)
-- Name: system_usr_fk1; Type: INDEX; Schema: public; Owner: general
--

CREATE UNIQUE INDEX system_usr_fk1 ON system_usr USING btree (system_id, user_no);


--
-- TOC entry 164 (OID 4844524)
-- Name: request_sk2; Type: INDEX; Schema: public; Owner: general
--

CREATE INDEX request_sk2 ON request USING btree (system_id) WHERE active;


--
-- TOC entry 173 (OID 4844555)
-- Name: request_timesheet_etag_skey; Type: INDEX; Schema: public; Owner: general
--

CREATE UNIQUE INDEX request_timesheet_etag_skey ON request_timesheet USING btree (work_by_id, dav_etag);


--
-- TOC entry 153 (OID 4843551)
-- Name: supported_locales_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY supported_locales
    ADD CONSTRAINT supported_locales_pkey PRIMARY KEY (locale);


--
-- TOC entry 155 (OID 4843564)
-- Name: usr_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY usr
    ADD CONSTRAINT usr_pkey PRIMARY KEY (user_no);


--
-- TOC entry 157 (OID 4843573)
-- Name: usr_setting_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY usr_setting
    ADD CONSTRAINT usr_setting_pkey PRIMARY KEY (user_no, setting_name);


--
-- TOC entry 158 (OID 4843588)
-- Name: roles_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (role_no);


--
-- TOC entry 159 (OID 4843611)
-- Name: session_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY "session"
    ADD CONSTRAINT session_pkey PRIMARY KEY (session_id);


--
-- TOC entry 160 (OID 4843648)
-- Name: organisation_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY organisation
    ADD CONSTRAINT organisation_pkey PRIMARY KEY (org_code);


--
-- TOC entry 161 (OID 4843671)
-- Name: request_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request
    ADD CONSTRAINT request_pkey PRIMARY KEY (request_id);


--
-- TOC entry 167 (OID 4843683)
-- Name: work_system_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY work_system
    ADD CONSTRAINT work_system_pkey PRIMARY KEY (system_code);


--
-- TOC entry 170 (OID 4843707)
-- Name: request_quote_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_quote
    ADD CONSTRAINT request_quote_pkey PRIMARY KEY (quote_id);

ALTER TABLE request_quote CLUSTER ON request_quote_pkey;


--
-- TOC entry 174 (OID 4843725)
-- Name: request_timesheet_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_timesheet
    ADD CONSTRAINT request_timesheet_pkey PRIMARY KEY (timesheet_id);


--
-- TOC entry 178 (OID 4843735)
-- Name: timesheet_note_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY timesheet_note
    ADD CONSTRAINT timesheet_note_pkey PRIMARY KEY (note_date, note_by_id);


--
-- TOC entry 179 (OID 4843743)
-- Name: request_note_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_note
    ADD CONSTRAINT request_note_pkey PRIMARY KEY (request_id, note_on);

ALTER TABLE request_note CLUSTER ON request_note_pkey;


--
-- TOC entry 181 (OID 4843759)
-- Name: request_request_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_request
    ADD CONSTRAINT request_request_pkey PRIMARY KEY (request_id, link_type, to_request_id);


--
-- TOC entry 184 (OID 4843779)
-- Name: request_attachment_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_attachment
    ADD CONSTRAINT request_attachment_pkey PRIMARY KEY (attachment_id);

ALTER TABLE request_attachment CLUSTER ON request_attachment_pkey;


--
-- TOC entry 188 (OID 4843799)
-- Name: attachment_type_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY attachment_type
    ADD CONSTRAINT attachment_type_pkey PRIMARY KEY (type_code);


--
-- TOC entry 192 (OID 4843829)
-- Name: saved_queries_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY saved_queries
    ADD CONSTRAINT saved_queries_pkey PRIMARY KEY (user_no, query_name);


--
-- TOC entry 193 (OID 4843836)
-- Name: help_hit_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY help_hit
    ADD CONSTRAINT help_hit_pkey PRIMARY KEY (user_no, topic);


--
-- TOC entry 194 (OID 4843843)
-- Name: help_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY help
    ADD CONSTRAINT help_pkey PRIMARY KEY (topic, seq);


--
-- TOC entry 195 (OID 4843855)
-- Name: infonode_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY infonode
    ADD CONSTRAINT infonode_pkey PRIMARY KEY (node_id);


--
-- TOC entry 198 (OID 4843868)
-- Name: wu_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY wu
    ADD CONSTRAINT wu_pkey PRIMARY KEY (node_id, wu_by);


--
-- TOC entry 201 (OID 4843875)
-- Name: wu_vote_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY wu_vote
    ADD CONSTRAINT wu_vote_pkey PRIMARY KEY (node_id, wu_by, vote_by);


--
-- TOC entry 202 (OID 4843879)
-- Name: nodetrack_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY nodetrack
    ADD CONSTRAINT nodetrack_pkey PRIMARY KEY (node_from, node_to);


--
-- TOC entry 204 (OID 4843892)
-- Name: organisation_tag_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY organisation_tag
    ADD CONSTRAINT organisation_tag_pkey PRIMARY KEY (tag_id);


--
-- TOC entry 206 (OID 4843902)
-- Name: request_tag_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_tag
    ADD CONSTRAINT request_tag_pkey PRIMARY KEY (request_id, tag_id);

ALTER TABLE request_tag CLUSTER ON request_tag_pkey;


--
-- TOC entry 209 (OID 4843942)
-- Name: pk_qa_approval; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_approval
    ADD CONSTRAINT pk_qa_approval PRIMARY KEY (qa_step_id, qa_approval_type_id);


--
-- TOC entry 210 (OID 4843949)
-- Name: pk_qa_approval_type; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_approval_type
    ADD CONSTRAINT pk_qa_approval_type PRIMARY KEY (qa_approval_type_id);


--
-- TOC entry 211 (OID 4843956)
-- Name: pk_qa_document; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_document
    ADD CONSTRAINT pk_qa_document PRIMARY KEY (qa_document_id);


--
-- TOC entry 212 (OID 4843964)
-- Name: pk_qa_model; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_model
    ADD CONSTRAINT pk_qa_model PRIMARY KEY (qa_model_id);


--
-- TOC entry 213 (OID 4843971)
-- Name: pk_qa_model_documents; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_model_documents
    ADD CONSTRAINT pk_qa_model_documents PRIMARY KEY (qa_model_id, qa_document_id);


--
-- TOC entry 214 (OID 4843975)
-- Name: pk_qa_model_step; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_model_step
    ADD CONSTRAINT pk_qa_model_step PRIMARY KEY (qa_model_id, qa_step_id);


--
-- TOC entry 215 (OID 4843983)
-- Name: pk_qa_phase; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_phase
    ADD CONSTRAINT pk_qa_phase PRIMARY KEY (qa_phase);


--
-- TOC entry 216 (OID 4843991)
-- Name: pk_qa_project_approval; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_approval
    ADD CONSTRAINT pk_qa_project_approval PRIMARY KEY (qa_approval_id);


--
-- TOC entry 217 (OID 4843998)
-- Name: pk_qa_project_step; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_step
    ADD CONSTRAINT pk_qa_project_step PRIMARY KEY (project_id, qa_step_id);


--
-- TOC entry 218 (OID 4844006)
-- Name: pk_qa_project_step_approval; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_step_approval
    ADD CONSTRAINT pk_qa_project_step_approval PRIMARY KEY (project_id, qa_step_id, qa_approval_type_id);


--
-- TOC entry 219 (OID 4844016)
-- Name: pk_qa_step; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_step
    ADD CONSTRAINT pk_qa_step PRIMARY KEY (qa_step_id);


--
-- TOC entry 220 (OID 4844023)
-- Name: pk_request_project; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_project
    ADD CONSTRAINT pk_request_project PRIMARY KEY (request_id);


--
-- TOC entry 190 (OID 4844360)
-- Name: ugroup_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY ugroup
    ADD CONSTRAINT ugroup_pkey PRIMARY KEY (group_no);


--
-- TOC entry 189 (OID 4844362)
-- Name: ugroup_group_name_key; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY ugroup
    ADD CONSTRAINT ugroup_group_name_key UNIQUE (group_name);


--
-- TOC entry 221 (OID 4844367)
-- Name: group_member_user_no_key; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY group_member
    ADD CONSTRAINT group_member_user_no_key UNIQUE (user_no, group_no);

ALTER TABLE group_member CLUSTER ON group_member_user_no_key;


--
-- TOC entry 180 (OID 4844384)
-- Name: request_interested_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_interested
    ADD CONSTRAINT request_interested_pkey PRIMARY KEY (request_id, user_no);

ALTER TABLE request_interested CLUSTER ON request_interested_pkey;


--
-- TOC entry 169 (OID 4844400)
-- Name: request_status_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_status
    ADD CONSTRAINT request_status_pkey PRIMARY KEY (request_id, status_on);

ALTER TABLE request_status CLUSTER ON request_status_pkey;


--
-- TOC entry 172 (OID 4844408)
-- Name: request_allocated_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_allocated
    ADD CONSTRAINT request_allocated_pkey PRIMARY KEY (request_id, allocated_to_id);

ALTER TABLE request_allocated CLUSTER ON request_allocated_pkey;


--
-- TOC entry 222 (OID 4844470)
-- Name: request_qa_action_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_qa_action
    ADD CONSTRAINT request_qa_action_pkey PRIMARY KEY (request_id, action_on);


--
-- TOC entry 223 (OID 4844561)
-- Name: caldav_data_pkey; Type: CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY caldav_data
    ADD CONSTRAINT caldav_data_pkey PRIMARY KEY (user_no, dav_name);


--
-- TOC entry 262 (OID 4843575)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY usr_setting
    ADD CONSTRAINT "$1" FOREIGN KEY (user_no) REFERENCES usr(user_no);


--
-- TOC entry 263 (OID 4843593)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY role_member
    ADD CONSTRAINT "$1" FOREIGN KEY (role_no) REFERENCES roles(role_no);


--
-- TOC entry 264 (OID 4843597)
-- Name: $2; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY role_member
    ADD CONSTRAINT "$2" FOREIGN KEY (user_no) REFERENCES usr(user_no);


--
-- TOC entry 265 (OID 4843613)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY "session"
    ADD CONSTRAINT "$1" FOREIGN KEY (user_no) REFERENCES usr(user_no);


--
-- TOC entry 266 (OID 4843624)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY tmp_password
    ADD CONSTRAINT "$1" FOREIGN KEY (user_no) REFERENCES usr(user_no);


--
-- TOC entry 260 (OID 4843651)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY usr
    ADD CONSTRAINT "$1" FOREIGN KEY (org_code) REFERENCES organisation(org_code);


--
-- TOC entry 279 (OID 4843894)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY organisation_tag
    ADD CONSTRAINT "$1" FOREIGN KEY (org_code) REFERENCES organisation(org_code);


--
-- TOC entry 280 (OID 4843904)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_tag
    ADD CONSTRAINT "$1" FOREIGN KEY (request_id) REFERENCES request(request_id);


--
-- TOC entry 281 (OID 4843908)
-- Name: $2; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_tag
    ADD CONSTRAINT "$2" FOREIGN KEY (tag_id) REFERENCES organisation_tag(tag_id);


--
-- TOC entry 282 (OID 4844025)
-- Name: fk_qa_approval_step; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_approval
    ADD CONSTRAINT fk_qa_approval_step FOREIGN KEY (qa_step_id) REFERENCES qa_step(qa_step_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 283 (OID 4844029)
-- Name: fk_qa_approval_type; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_approval
    ADD CONSTRAINT fk_qa_approval_type FOREIGN KEY (qa_approval_type_id) REFERENCES qa_approval_type(qa_approval_type_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 284 (OID 4844033)
-- Name: fk_documents_model; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_model_documents
    ADD CONSTRAINT fk_documents_model FOREIGN KEY (qa_model_id) REFERENCES qa_model(qa_model_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 285 (OID 4844037)
-- Name: fk_model_documents; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_model_documents
    ADD CONSTRAINT fk_model_documents FOREIGN KEY (qa_document_id) REFERENCES qa_document(qa_document_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 286 (OID 4844041)
-- Name: fk_qa_model_step; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_model_step
    ADD CONSTRAINT fk_qa_model_step FOREIGN KEY (qa_step_id) REFERENCES qa_step(qa_step_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 287 (OID 4844045)
-- Name: fk_qa_model_step_model; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_model_step
    ADD CONSTRAINT fk_qa_model_step_model FOREIGN KEY (qa_model_id) REFERENCES qa_model(qa_model_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 288 (OID 4844049)
-- Name: fk_proj_approval_type; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_approval
    ADD CONSTRAINT fk_proj_approval_type FOREIGN KEY (qa_approval_type_id) REFERENCES qa_approval_type(qa_approval_type_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 289 (OID 4844053)
-- Name: fk_proj_qa_approval_usr; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_approval
    ADD CONSTRAINT fk_proj_qa_approval_usr FOREIGN KEY (approval_by_usr) REFERENCES usr(user_no) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 290 (OID 4844057)
-- Name: fk_proj_qa_behalf_of_usr; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_approval
    ADD CONSTRAINT fk_proj_qa_behalf_of_usr FOREIGN KEY (assigned_to_usr) REFERENCES usr(user_no) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 291 (OID 4844061)
-- Name: fk_project_qa_approval_step; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_approval
    ADD CONSTRAINT fk_project_qa_approval_step FOREIGN KEY (project_id, qa_step_id) REFERENCES qa_project_step(project_id, qa_step_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 292 (OID 4844065)
-- Name: fk_proj_qa_step_project; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_step
    ADD CONSTRAINT fk_proj_qa_step_project FOREIGN KEY (project_id) REFERENCES request_project(request_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 293 (OID 4844069)
-- Name: fk_qa_proj_step_reqid; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_step
    ADD CONSTRAINT fk_qa_proj_step_reqid FOREIGN KEY (request_id) REFERENCES request(request_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 294 (OID 4844073)
-- Name: fk_qa_proje_fk_qa_ste_qa_step; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_step
    ADD CONSTRAINT fk_qa_proje_fk_qa_ste_qa_step FOREIGN KEY (qa_step_id) REFERENCES qa_step(qa_step_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 295 (OID 4844077)
-- Name: fk_qa_proje_reference_usr; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_step
    ADD CONSTRAINT fk_qa_proje_reference_usr FOREIGN KEY (responsible_usr) REFERENCES usr(user_no) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- TOC entry 296 (OID 4844081)
-- Name: fk_proj_step_appr_type; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_step_approval
    ADD CONSTRAINT fk_proj_step_appr_type FOREIGN KEY (qa_approval_type_id) REFERENCES qa_approval_type(qa_approval_type_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 297 (OID 4844085)
-- Name: fk_proj_step_approval; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_project_step_approval
    ADD CONSTRAINT fk_proj_step_approval FOREIGN KEY (project_id, qa_step_id) REFERENCES qa_project_step(project_id, qa_step_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 298 (OID 4844089)
-- Name: fk_qa_step_document; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_step
    ADD CONSTRAINT fk_qa_step_document FOREIGN KEY (qa_document_id) REFERENCES qa_document(qa_document_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 299 (OID 4844093)
-- Name: fk_qa_step_phase; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY qa_step
    ADD CONSTRAINT fk_qa_step_phase FOREIGN KEY (qa_phase) REFERENCES qa_phase(qa_phase) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 300 (OID 4844097)
-- Name: fk_project_phase; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_project
    ADD CONSTRAINT fk_project_phase FOREIGN KEY (qa_phase) REFERENCES qa_phase(qa_phase) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 301 (OID 4844101)
-- Name: fk_qa_mentor_usr; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_project
    ADD CONSTRAINT fk_qa_mentor_usr FOREIGN KEY (qa_mentor) REFERENCES usr(user_no) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 302 (OID 4844105)
-- Name: fk_qa_project_mgr_usr; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_project
    ADD CONSTRAINT fk_qa_project_mgr_usr FOREIGN KEY (project_manager) REFERENCES usr(user_no) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 303 (OID 4844109)
-- Name: fk_req_proj_qa_model; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_project
    ADD CONSTRAINT fk_req_proj_qa_model FOREIGN KEY (qa_model_id) REFERENCES qa_model(qa_model_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 304 (OID 4844113)
-- Name: fk_request__fk_req_pr_request; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_project
    ADD CONSTRAINT fk_request__fk_req_pr_request FOREIGN KEY (request_id) REFERENCES request(request_id) ON UPDATE RESTRICT ON DELETE RESTRICT DEFERRABLE INITIALLY DEFERRED;


--
-- TOC entry 275 (OID 4844314)
-- Name: request_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_note
    ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);


--
-- TOC entry 271 (OID 4844318)
-- Name: request_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_status
    ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);


--
-- TOC entry 274 (OID 4844322)
-- Name: request_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_timesheet
    ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);


--
-- TOC entry 272 (OID 4844326)
-- Name: request_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_quote
    ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);


--
-- TOC entry 276 (OID 4844330)
-- Name: request_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_interested
    ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);


--
-- TOC entry 273 (OID 4844334)
-- Name: request_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_allocated
    ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);


--
-- TOC entry 277 (OID 4844338)
-- Name: request_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_attachment
    ADD CONSTRAINT request_id_fk FOREIGN KEY (request_id) REFERENCES request(request_id);


--
-- TOC entry 267 (OID 4844342)
-- Name: requester_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request
    ADD CONSTRAINT requester_fk FOREIGN KEY (requester_id) REFERENCES usr(user_no);


--
-- TOC entry 268 (OID 4844346)
-- Name: creator_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request
    ADD CONSTRAINT creator_fk FOREIGN KEY (entered_by) REFERENCES usr(user_no);


--
-- TOC entry 261 (OID 4844350)
-- Name: organisation_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY usr
    ADD CONSTRAINT organisation_fk FOREIGN KEY (org_code) REFERENCES organisation(org_code);


--
-- TOC entry 305 (OID 4844369)
-- Name: group_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY group_member
    ADD CONSTRAINT group_fk FOREIGN KEY (group_no) REFERENCES ugroup(group_no);


--
-- TOC entry 306 (OID 4844373)
-- Name: user_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY group_member
    ADD CONSTRAINT user_fk FOREIGN KEY (user_no) REFERENCES usr(user_no);


--
-- TOC entry 307 (OID 4844472)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_qa_action
    ADD CONSTRAINT "$1" FOREIGN KEY (request_id) REFERENCES request(request_id);


--
-- TOC entry 308 (OID 4844476)
-- Name: $2; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request_qa_action
    ADD CONSTRAINT "$2" FOREIGN KEY (action_by) REFERENCES usr(user_no);


--
-- TOC entry 270 (OID 4844510)
-- Name: system_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY org_system
    ADD CONSTRAINT system_id_fk FOREIGN KEY (system_id) REFERENCES work_system(system_id);


--
-- TOC entry 278 (OID 4844515)
-- Name: system_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY system_usr
    ADD CONSTRAINT system_id_fk FOREIGN KEY (system_id) REFERENCES work_system(system_id);


--
-- TOC entry 269 (OID 4844519)
-- Name: system_id_fk; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY request
    ADD CONSTRAINT system_id_fk FOREIGN KEY (system_id) REFERENCES work_system(system_id);


--
-- TOC entry 309 (OID 4844563)
-- Name: $1; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY caldav_data
    ADD CONSTRAINT "$1" FOREIGN KEY (user_no) REFERENCES usr(user_no);


--
-- TOC entry 310 (OID 4844567)
-- Name: $2; Type: FK CONSTRAINT; Schema: public; Owner: general
--

ALTER TABLE ONLY caldav_data
    ADD CONSTRAINT "$2" FOREIGN KEY (logged_user) REFERENCES usr(user_no);


SET SESSION AUTHORIZATION 'postgres';

--
-- TOC entry 3 (OID 2200)
-- Name: SCHEMA public; Type: COMMENT; Schema: -; Owner: postgres
--

COMMENT ON SCHEMA public IS 'Standard public schema';


SET SESSION AUTHORIZATION 'general';

--
-- TOC entry 91 (OID 4843939)
-- Name: TABLE qa_approval; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON TABLE qa_approval IS 'Contains the required Quality Assurance Approvals for given QA Step. A QA Approval is associated with a given QA Step. The contents of this table define which approvals records have to be created for QA Steps, when you create the QA instance records for a project.';


--
-- TOC entry 94 (OID 4843944)
-- Name: TABLE qa_approval_type; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON TABLE qa_approval_type IS 'Contains Quality Assurance Approval Types. A QA Approval Type represents a particular kind of approval required for a QA Step. Examples would be ''Internal Approval'', ''Peer Review'', ''Maintainer Approval'', or ''Client Approval''.';


--
-- TOC entry 99 (OID 4843958)
-- Name: TABLE qa_model; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON TABLE qa_model IS 'Contains Quality Assurance models. A model is simply a hypothetical QA profile which defines the QA requirements
for that profile. It provides a kind of template for assigning default QA Steps etc. There are three simple models
which have been invented to begin with: Small, Medium and Large (referring to project size).';


--
-- TOC entry 106 (OID 4843977)
-- Name: TABLE qa_phase; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON TABLE qa_phase IS 'Contains all of the Quality Assurance Phases available. A QA Phase is a logical grouping of QA Steps. Useful for display and reporting purposes.';


--
-- TOC entry 109 (OID 4843985)
-- Name: TABLE qa_project_approval; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON TABLE qa_project_approval IS 'Contains Quality Assurance Approvals. A QA Approval record is associated with a given project QA Step and is only created when someone who is permitted to do so seeks to acquire an approval for the given QA Step. NB: QA Approval records are ''read-only'' to the QA application - they are an audit trail of approval activities. A QA user can create as many approvals for the same project, QA Step and Approval Type as they wish - they just keep adding to the approval audit trail.';


--
-- TOC entry 110 (OID 4843985)
-- Name: COLUMN qa_project_approval.assigned_to_usr; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_approval.assigned_to_usr IS 'The user that the approval process is assigned to - by the Project Manager. The approval is then approved by someone allocated to the project, or the project manager or the QA mentor (all are permitted to do it), however if the assigned user does not match the approved-by user, the approval has been explicitly over-ridden.';


--
-- TOC entry 111 (OID 4843985)
-- Name: COLUMN qa_project_approval.approval_by_usr; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_approval.approval_by_usr IS 'The user who is updating this approval status.';


--
-- TOC entry 112 (OID 4843985)
-- Name: COLUMN qa_project_approval.approval_datetime; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_approval.approval_datetime IS 'Time and date that the approval status was changed to the current status.';


--
-- TOC entry 113 (OID 4843985)
-- Name: COLUMN qa_project_approval."comment"; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_approval."comment" IS 'Used to make brief comments on this approval.';


--
-- TOC entry 116 (OID 4843993)
-- Name: TABLE qa_project_step; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON TABLE qa_project_step IS 'The Project QA Step table contains the QA Steps defined for a given project. Each step is associated with a WRMS request record, which can be used to attach QA documents etc. and also for final signoff of the task once all the required approvals have been acquired.';


--
-- TOC entry 117 (OID 4843993)
-- Name: COLUMN qa_project_step.project_id; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_step.project_id IS 'The unique ID for a project. This is actually a WRMS request ID of the master WRMS record  created for this project.';


--
-- TOC entry 118 (OID 4843993)
-- Name: COLUMN qa_project_step.qa_step_id; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_step.qa_step_id IS 'This is the QA Step being processed for the given project.';


--
-- TOC entry 119 (OID 4843993)
-- Name: COLUMN qa_project_step.request_id; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_step.request_id IS 'This is the foreign key to the WRMS record for this QA Step. This WRMS record is used during the processing of this QA Step, for attaching docs, making notes etc. in the usual WRMS fashion.';


--
-- TOC entry 120 (OID 4843993)
-- Name: COLUMN qa_project_step.responsible_usr; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_step.responsible_usr IS 'This user is assigned to the QA Step as the person responsible for delivering it and getting it approved. The person is selected from those allocated to the project.';


--
-- TOC entry 121 (OID 4843993)
-- Name: COLUMN qa_project_step.responsible_datetime; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_step.responsible_datetime IS 'The datetime that the user responsible for this step was assigned to it.';


--
-- TOC entry 124 (OID 4844000)
-- Name: TABLE qa_project_step_approval; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON TABLE qa_project_step_approval IS 'This contains the list of approval types which are required for a given project QA step. It starts off as the default types as expressed by the ''qa_approval'' table, but may be subsequently modified by the project manager to add or subtract approval types. The presence of one of these records indicates that the given approval type is required for the project QA step. Note that this record also holds a denormalised value of the last approval status registered for this type.';


--
-- TOC entry 125 (OID 4844000)
-- Name: COLUMN qa_project_step_approval.project_id; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_step_approval.project_id IS 'The unique ID for a project. This is actually a WRMS request ID of the master WRMS record  created for this project.';


--
-- TOC entry 126 (OID 4844000)
-- Name: COLUMN qa_project_step_approval.qa_step_id; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN qa_project_step_approval.qa_step_id IS 'This is the QA Step being processed for the given project.';


--
-- TOC entry 129 (OID 4844008)
-- Name: TABLE qa_step; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON TABLE qa_step IS 'Contains all of the Quality Assurance Steps that are allowed in a project. A QA Step is a task which needs to be achieved as part of the QA process, and must be QA approved.';


--
-- TOC entry 132 (OID 4844018)
-- Name: TABLE request_project; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON TABLE request_project IS 'Contains the master records for projects. Every project has a master WRMS record associated with it, and this table contains pointers to those.';


--
-- TOC entry 133 (OID 4844018)
-- Name: COLUMN request_project.project_manager; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN request_project.project_manager IS 'The user who is the designated project manager for this project.';


--
-- TOC entry 134 (OID 4844018)
-- Name: COLUMN request_project.qa_mentor; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN request_project.qa_mentor IS 'The user who is designated as the person to help out and guide the project team in quality assurance matters.';


--
-- TOC entry 135 (OID 4844018)
-- Name: COLUMN request_project.qa_model_id; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN request_project.qa_model_id IS 'The initial choice by the project creator, of the model that the project is closest to in size.';


--
-- TOC entry 136 (OID 4844018)
-- Name: COLUMN request_project.qa_phase; Type: COMMENT; Schema: public; Owner: general
--

COMMENT ON COLUMN request_project.qa_phase IS 'The current phase that the project is in. Updated whenever approval action takes place. Can be used as a means of high-level project progress viewing.';

\i base-data.sql
