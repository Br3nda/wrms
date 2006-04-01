/*==============================================================*/
/*                                                              */
/*  Database patch for WRMS                                     */
/*  This patch creates the entities required for QAMS to work   */
/*  with WRMS v2, in addition to the core QAMS schema which     */
/*  should have been loaded prior to this script being run.     */
/*                                                              */
/*==============================================================*/


-- Extra request type for QA steps..
insert into lookup_code (source_table, source_field, lookup_seq, lookup_code, lookup_desc) values ('request', 'request_type', 90, '90', 'Quality Assurance');

-- Extra user group (role) for Quality Assurance..
insert into ugroup (module_name, group_name, seq)
 values ('qams', 'QA', 500);

-- Make QA working party members QA roled already..
-- Paul W
insert into group_member (user_no, group_no) values (5, 8);
-- -- Farai
-- insert into group_member (user_no, group_no) values (239, 8);
-- John P
insert into group_member (user_no, group_no) values (342, 8);


-- Set all the sequences up..
select setval('seq_qa_document_id', (select max(qa_document_id) from qa_document));
select setval('seq_qa_model_id', (select max(qa_model_id) from qa_model));
select setval('seq_qa_step_id', (select max(qa_step_id) from qa_step));
select setval('seq_qa_approval_type_id', (select max(qa_approval_type_id) from qa_approval_type));

-- Grant general user permissions..
grant select on qa_phase to general;
grant select on qa_step to general;
grant select on qa_approval to general;
grant select on qa_approval_type to general;
grant select on qa_model to general;
grant select on qa_model_step to general;
grant select on qa_document to general;
grant select on qa_model_documents to general;

grant select,update,insert,delete on request_project to general;
grant select,update,insert,delete on qa_project_step to general;
grant select,update,insert,delete on qa_project_step_approval to general;
grant select,update,insert,delete on qa_project_approval to general;

grant select,update on 
	seq_qa_approval_type_id,  
	seq_qa_document_id, 
	seq_qa_model_id, 
	seq_qa_step_id
 to general;
 