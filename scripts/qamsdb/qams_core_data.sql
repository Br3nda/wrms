--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 2 (OID 66565400)
-- Name: qa_phase; Type: TABLE DATA; Schema: public; Owner: paul
--

INSERT INTO qa_phase (qa_phase, qa_phase_desc, qa_phase_order) VALUES ('Plan', 'Plan', 40);
INSERT INTO qa_phase (qa_phase, qa_phase_desc, qa_phase_order) VALUES ('Build', 'Build', 50);
INSERT INTO qa_phase (qa_phase, qa_phase_desc, qa_phase_order) VALUES ('Integrate', 'Integrate', 60);
INSERT INTO qa_phase (qa_phase, qa_phase_desc, qa_phase_order) VALUES ('Test', 'Test', 70);
INSERT INTO qa_phase (qa_phase, qa_phase_desc, qa_phase_order) VALUES ('Install', 'Install', 80);
INSERT INTO qa_phase (qa_phase, qa_phase_desc, qa_phase_order) VALUES ('Concept', 'Concept', 10);
INSERT INTO qa_phase (qa_phase, qa_phase_desc, qa_phase_order) VALUES ('Define', 'Define', 20);
INSERT INTO qa_phase (qa_phase, qa_phase_desc, qa_phase_order) VALUES ('Design', 'Design', 30);


--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 2 (OID 66565943)
-- Name: qa_document; Type: TABLE DATA; Schema: public; Owner: paul
--

INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (13, 'Test Plan', 'A test plan document which has been produced for its associated QA Step according to the Verification and Validation Plan.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (3, 'Concept Document', 'The concept document describes the initial requirements and aspirations of the project in a high level way. It compliments the preliminary functional requirements document by providing more context, and explaining in high level terms why the project is being undertaken.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (4, 'Functional Specification', 'The functional specification is a ''lite'' version of a full Requirements Specification. It should contain a description of all the known key functions at the early stages of the project, and is the springboard for the Requirements Specification document.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (6, 'Feasibility Study', 'A feasibility study should look thoroughly at technical, cost and other issues which might bear on a go/no-go decision on the project.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (7, 'Risk Analysis', 'A risk analysis should identify all risks which might affect the project and explore each one thoroughly.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (10, 'Maintenance Plan', 'The maintenance plan contains all of the details of how the system will be maintained once it has been installed/delivered.

Note that this document is a superset of the Maintainance Manual, and may even physically contain that manual as a sub-section/appendix.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (14, 'Disaster Recovery Plan', 'The disaster recovery plan contains details of the procedures and tools to be used in all of the anticipated critical failure modes of the system.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (29, 'Installation Plan', 'The installation plan should cover all of the steps required to install the system, an installation schedule, details of resources required, and any applicable costs.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (15, 'Maintenance Manual', 'The maintenance manual is a subset/off-shoot of the Maintenance Plan. It contains the nitty-gritty information required for System Administrators and Maintainers to maintain and/or trouble-shoot the system in production.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (20, 'Post-installation Report', 'The post-installation report is essentially a de-brief of the project at the end, describing what went right and what went wrong. Anything is fair game for comment, including QA processes, project management, development tools, environment etc.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (5, 'Preliminary Design Document', 'The preliminary design is a ''lite'' version of a detailed design document. It should express how the functional elements described in the Functional Specification could be built.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (2, 'Preliminary Project Plan', 'The preliminary project plan is the first stab at estimating resources required, and cost. It should also provide a proposed delivery schedule.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (11, 'Project Plan', 'The project plan uses all of the documentation on requirements, concept, functional spec. etc. in order to fully resource, cost, and schedule the project. It should provide a delivery schedule, and a work breakdown structure (eg. Gantt chart) of the project tasks.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (8, 'Requirements Specification', 'The requirements specification is the repository of all of the key things that the client requires in the finished system. It also forms the basis of the project test plans.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (19, 'Training Material', 'Training material is generically any kind of documentation or media which is produced in order to facilitate education in the use of or maintenance of the system being built.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (16, 'User Manual', 'A user manual is documentation aimed at the end-users of the system being built, and is provided to teach them how to use it fully.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (12, 'Verification and Validation Plan', 'A Verification and Validation plan is a document which describes the way the system has to be tested. This encompasses all testing, for eaxmple - unit testing, module testing, integration testing, site acceptance, customer acceptance etc. The VVP should contain the necessary detail as to content and procedures to be adopted for testing.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (1, 'Quality Assurance Plan', 'The quality assurance plan contains details of how Quality Assurance is going to be applied to the project. As a minimum, it provides a schedule of documents to be delivered, and a schedule of Quality Assurance Steps, which will require formal approvals.

Note: QAMS provides this facility built into its QA Step definition interface, therefore there is no separate paper-based QA Plan.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (18, 'Integration Test Results', 'A document which contains test results produced according to the Verification and Validation Plan.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (37, 'Site Acceptance Test Results', 'A document which contains test results produced according to the Verification and Validation Plan.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (38, 'Customer Acceptance Test Results', 'A document which contains test results produced according to the Verification and Validation Plan.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (39, 'Regression Test Results', 'A document which contains test results produced according to the Verification and Validation Plan.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (17, 'Code Review', 'A code review is a report on various aspects of the coding which has been done in the build phase. It should contain a section describing what is being reviewed, and how the review was done. As well as confirming the areas which pass muster, the review should report on any major problems, and make suggestions as to how these might be resolved.');
INSERT INTO qa_document (qa_document_id, qa_document_title, qa_document_desc) VALUES (9, 'Detailed Design Document', 'The detailed design document takes the requirements and describes how these will be implemented. It should also describe how the system environment will be built, and provide details of all interfaces to any external systems that it relies on.');


--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 2 (OID 66566843)
-- Name: qa_model; Type: TABLE DATA; Schema: public; Owner: paul
--

INSERT INTO qa_model (qa_model_id, qa_model_name, qa_model_desc, qa_model_order) VALUES (3, 'Large', 'Quality assurance model for a medium-sized project of more than 10 person-weeks.', 30);
INSERT INTO qa_model (qa_model_id, qa_model_name, qa_model_desc, qa_model_order) VALUES (1, 'Small', 'Quality assurance model for small projects of approximately 1 - 3 person-weeks.', 10);
INSERT INTO qa_model (qa_model_id, qa_model_name, qa_model_desc, qa_model_order) VALUES (2, 'Medium', 'Quality assurance model for a medium-sized project of 4 - 10 person-weeks.', 20);


--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 2 (OID 66565393)
-- Name: qa_approval_type; Type: TABLE DATA; Schema: public; Owner: paul
--

INSERT INTO qa_approval_type (qa_approval_type_id, qa_approval_type_desc) VALUES (2, 'Client Approval');
INSERT INTO qa_approval_type (qa_approval_type_id, qa_approval_type_desc) VALUES (1, 'Internal Peer Review');
INSERT INTO qa_approval_type (qa_approval_type_id, qa_approval_type_desc) VALUES (5, 'QA Auditor Signoff');
INSERT INTO qa_approval_type (qa_approval_type_id, qa_approval_type_desc) VALUES (3, 'Director Signoff');
INSERT INTO qa_approval_type (qa_approval_type_id, qa_approval_type_desc) VALUES (4, 'Sysadmin Signoff');


--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 2 (OID 66565432)
-- Name: qa_step; Type: TABLE DATA; Schema: public; Owner: paul
--

INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (3, 'Concept', 'Review concept document', 30, true, 'Does the concept document adequately describe what the project is all about and why it is being done?', 3, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (2, 'Concept', 'Review preliminary project plan', 20, true, 'Review preliminary project plan. The plan should contain solid estimates of resources required, and cost. It should also provide an initially proposed delivery schedule.', 2, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (4, 'Concept', 'Review functional specification', 40, true, 'Does the functional specification contain a description of all the known key functions at this early stage, and is it good enough to support good project plan estimates? Is it an adequate start for a full requirements specification?', 4, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (5, 'Concept', 'Review preliminary design', 50, true, 'Does the preliminiary design cover all of the functional requirements, as per the functional requirements specification, and would it provide a good platform for a detailed design?', 5, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (6, 'Concept', 'Review feasibility study', 60, true, 'Does the feasibility study cover all of the critical areas of concern, for example cost, performance, technical difficulties etc? Does it answer all of the questions that need to be answered in order to decide whether to go ahead with the project?', 6, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (7, 'Concept', 'Review risk analysis', 70, true, 'First of all, does the risk analysis fully identify all of the risks facing the project? Does it properly adress each of them?', 7, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (8, 'Define', 'Review requirements specification', 80, true, 'Does the requirements specification cover all of the key functions that are required? Is it written in such a way that the main stataments in it are testable? Would it support the writing of a detailed design document, and all of the test plans required by the project?', 8, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (9, 'Design', 'Review detailed design document', 90, true, 'Does the design address all of the function points described in the requirements documentation? Does it provide developers with enough information to implement the system? Does it contain enough detail to enable the maintenance plan and maintenance manuals to be written?', 9, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (17, 'Build', 'Project tracking reviews', 170, true, 'Are the project tracking procedures adequate, and are they being followed?', NULL, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (10, 'Plan', 'Review preliminary maintenance plan', 100, true, 'Does the preliminary maintenance plan contain all of the relevant sections which need to be covered? Does it look like it will deliver enough information for maintainers to do their job when the system is running live?', 10, true);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (11, 'Plan', 'Review project plan', 110, true, 'Does the project plan contain sensible estimates for resources, and delivery schedule? Does it have enough detail? Does what the plan says tally with all the available information - eg. specifications, designs etc? In the end, the question is: is the plan achievable?', 11, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (12, 'Plan', 'Review validation & verification plan', 120, true, 'Does the VVP cover all of the aspects of testing which the QA Plan says are to be undertaken in the project? Does it provide enough detail for test plan authors, and testers to produce documents and do testing respectively?', 12, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (13, 'Plan', 'Review acceptance test plan', 130, true, 'Does the acceptance test plan fully cover all of the function points described in the requirements documentation? Can the test plan be given to a client, in the expectation they can go through all of the tests to verify that the system does what is required?', 13, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (14, 'Plan', 'Review disaster recovery plan', 140, true, 'Does the DRP adequately cover all of the necessary failure modesof the system? Does it provide a clear and implementable plan for each one?', 14, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (15, 'Build', 'Review maintenance manual', 150, true, 'Does the maintenance manual cover everything that a maintainer needs to know? Eg. does it provide enough information on the system operating environment(s), application software etc? Does it provide an adequate trouble-shooting guide? Does it cover the process for system upgrades and change management?', 15, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (16, 'Build', 'Review user manual', 160, true, 'Does the user manual cover everything about the system which a user needs to know to operate it? Is it well laid out, clear and easy to follow? Has user opinion on the manual been canvased, and has that feedback been actioned?', 16, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (18, 'Build', 'Code reviews', 180, true, 'Does the code meet the criteria and methodology described in the Code Review document and QA Plan?', 17, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (24, 'Integrate', 'Review installation plan', 240, true, 'Does the installation plan cover all aspects of the installation of the system? Does it consider basics, such as timeframes, resourcing, costs?  Is it achievable?', 29, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (26, 'Install', 'Review installed system', 260, true, 'Is the system properly installed in its production environment?', NULL, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (27, 'Install', 'Review maintenance plan', 270, true, 'Does the final maintenance plan contain everything that System Administrators and Maintainers need to keep the system running? Does it contain procedures for upgrade/change of the system?', 10, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (28, 'Install', 'Post-installation review', 280, true, 'Was a sufficiently in-depth post-installation review held? Did the ensuing report cover all of the required topics, and provide an adequate de-brief of what went right and what went wrong?', 20, true);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (20, 'Build', 'Review module testing', 200, true, 'Does the module testing in the project measure up to the plan described in the Verification & Validation Plan. Are the module tests implemented well and are they ensuring requirements are met?', NULL, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (19, 'Build', 'Review unit testing', 190, true, 'Does the unit testing in the project measure up to the plan described in the Verification & Validation Plan. Are the unit tests implemented well and are they ensuring requirements are met?', NULL, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (1, 'Concept', 'Review quality plan', 10, true, 'Review the quality plan to ensure that it is appropriate for this project. Does it cover all the steps you think are necessary, given the project size? Conversely, does it perhaps contain too many?

Note: the Quality Plan is not a separate document, but instead is defined by the Quality Assurance Steps which are set up in QAMS for this project.', NULL, true);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (23, 'Integrate', 'Review site acceptance test results', 230, true, 'Do the site acceptance test results show that the system has ''passed''?', 37, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (25, 'Test', 'Review customer acceptance test results', 250, true, 'Do the customer acceptance test results show that the system has ''passed''?', 38, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (29, 'Build', 'Review regression testing', 195, false, 'Is the regression testing being implemented as defined in the Verification & Validation Plan? Are the unit tests implemented well and are they ensuring requirements are met?', 39, false);
INSERT INTO qa_step (qa_step_id, qa_phase, qa_step_desc, qa_step_order, enabled, qa_step_notes, qa_document_id, mandatory) VALUES (21, 'Integrate', 'Review integration test results', 210, true, 'Do the integration test results show that the system has been fully integrated, and has ''passed''?', 18, false);


--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 2 (OID 66566848)
-- Name: qa_model_step; Type: TABLE DATA; Schema: public; Owner: paul
--

INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 1);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 2);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 3);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 4);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 5);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 8);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 9);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 10);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 11);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 12);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 13);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 15);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 17);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 18);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 19);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 29);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 20);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 21);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 23);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 24);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 25);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 26);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 27);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (3, 28);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (1, 1);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (1, 2);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (1, 3);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (1, 9);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (1, 15);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (1, 27);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (1, 28);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 1);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 2);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 3);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 4);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 8);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 9);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 11);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 12);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 13);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 15);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 18);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 21);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 25);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 26);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 27);
INSERT INTO qa_model_step (qa_model_id, qa_step_id) VALUES (2, 28);


--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 2 (OID 66572868)
-- Name: qa_model_documents; Type: TABLE DATA; Schema: public; Owner: paul
--

INSERT INTO qa_model_documents (qa_model_id, qa_document_id, path_to_template, path_to_example) VALUES (1, 1, '', '');
INSERT INTO qa_model_documents (qa_model_id, qa_document_id, path_to_template, path_to_example) VALUES (1, 9, '/qadoc/templates/3_design/T_Design_SP.sxw', '');
INSERT INTO qa_model_documents (qa_model_id, qa_document_id, path_to_template, path_to_example) VALUES (1, 17, '', '/qadoc/examples/5_build/Code_Review_SP.sxw');


--
-- PostgreSQL database dump
--

SET client_encoding = 'SQL_ASCII';
SET check_function_bodies = false;

SET search_path = public, pg_catalog;

--
-- Data for TOC entry 2 (OID 66565380)
-- Name: qa_approval; Type: TABLE DATA; Schema: public; Owner: paul
--

INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (3, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (3, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (2, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (2, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (4, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (4, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (5, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (6, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (7, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (8, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (8, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (9, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (10, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (11, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (11, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (12, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (12, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (13, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (13, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (14, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (14, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (15, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (16, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (16, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (18, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (21, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (26, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (27, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (28, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (19, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (1, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (1, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (23, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (23, 1, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (25, 2, 0);
INSERT INTO qa_approval (qa_step_id, qa_approval_type_id, qa_approval_order) VALUES (29, 1, 0);


