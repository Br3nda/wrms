-- Set permissions on Database objects

GRANT INSERT, UPDATE, SELECT ON
  request,
  request_quote,
  request_status, request_note,
  request_request, request_history,
  request_attachment,
  lookup_code,
  attachment_type,
  session,
  work_system,
  usr, usr_setting,
  organisation,
  help, help_hit,
  infonode, wu, wu_vote, nodetrack,
  qa_approval,
  qa_approval_type,
  qa_document,
  qa_model,
  qa_model_documents,
  qa_model_step,
  qa_phase,
  qa_project_approval,
  qa_project_step,
  qa_project_step_approval,
  qa_step,
  request_project,
  request_qa_action
  TO general;


GRANT INSERT,UPDATE,SELECT,DELETE ON
  request_timesheet,
  request_allocated, request_interested,
  org_system,
  timesheet_note,
  role_member,
  organisation_action,
  request_tag, organisation_tag,
  system_usr,
  saved_queries
  TO general;


-- Will fail on PostgreSQL 8.1 or older
GRANT USAGE, UPDATE, SELECT ON
  request_attachment_attachment_id_seq,
  request_request_id_seq,
  request_quote_quote_id_seq,
  session_session_id_seq,
  usr_user_no_seq,
  organisation_org_code_seq,
  infonode_node_id_seq,
  organisation_action_action_id_seq,
  organisation_tag_tag_id_seq,
  request_timesheet_timesheet_id_seq,
  work_system_system_id_seq,
  roles_role_no_seq,
  qa_approval_qa_step_id_seq,
  qa_approval_type_qa_approval_type_id_seq,
  qa_document_qa_document_id_seq,
  qa_model_qa_model_id_seq,
  qa_project_approval_qa_approval_id_seq,
  qa_step_qa_step_id_seq
  TO general;

-- Will succeed on PostgreSQL 8.1 or older
GRANT INSERT, UPDATE, SELECT ON
  request_attachment_attachment_id_seq,
  request_request_id_seq,
  request_quote_quote_id_seq,
  session_session_id_seq,
  usr_user_no_seq,
  organisation_org_code_seq,
  infonode_node_id_seq,
  organisation_action_action_id_seq,
  organisation_tag_tag_id_seq,
  request_timesheet_timesheet_id_seq,
  work_system_system_id_seq,
  roles_role_no_seq,
  qa_approval_qa_step_id_seq,
  qa_approval_type_qa_approval_type_id_seq,
  qa_document_qa_document_id_seq,
  qa_model_qa_model_id_seq,
  qa_project_approval_qa_approval_id_seq,
  qa_step_qa_step_id_seq
  TO general;


