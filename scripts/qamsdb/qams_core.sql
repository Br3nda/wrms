/*==============================================================*/
/* Database name:  axyl                                         */
/* DBMS name:      PostgreSQL 7                                 */
/* Created on:     24/12/2005 9:24:07 a.m.                      */
/*==============================================================*/


create sequence seq_qa_approval_id
increment 1
minvalue 1
maxvalue 2147483647
start 1
cache 1;

create sequence seq_qa_approval_type_id
increment 1
minvalue 1
maxvalue 2147483647
start 1
cache 1;

create sequence seq_qa_document_id
increment 1
minvalue 1
maxvalue 2147483647
start 1
cache 1;

create sequence seq_qa_model_id
increment 1
minvalue 1
maxvalue 2147483647
start 1
cache 1;

create sequence seq_qa_step_id
increment 1
minvalue 1
maxvalue 2147483647
start 1
cache 1;

/*==============================================================*/
/* Table: qa_approval                                           */
/*==============================================================*/
create table qa_approval (
qa_step_id           INT4                 not null,
qa_approval_type_id  INT4                 not null,
qa_approval_order    INT4                 not null default 0,
constraint PK_QA_APPROVAL primary key (qa_step_id, qa_approval_type_id)
);

comment on table qa_approval is
'Contains the required Quality Assurance Approvals for given QA Step. A QA Approval is associated with a given QA Step. The contents of this table define which approvals records have to be created for QA Steps, when you create the QA instance records for a project.';

/*==============================================================*/
/* Table: qa_approval_type                                      */
/*==============================================================*/
create table qa_approval_type (
qa_approval_type_id  INT4                 not null,
qa_approval_type_desc TEXT                 not null,
constraint PK_QA_APPROVAL_TYPE primary key (qa_approval_type_id)
);

comment on table qa_approval_type is
'Contains Quality Assurance Approval Types. A QA Approval Type represents a particular kind of approval required for a QA Step. Examples would be ''Internal Approval'', ''Peer Review'', ''Maintainer Approval'', or ''Client Approval''.';

/*==============================================================*/
/* Table: qa_document                                           */
/*==============================================================*/
create table qa_document (
qa_document_id       INT4                 not null,
qa_document_title    TEXT                 not null,
qa_document_desc     TEXT                 null,
constraint PK_QA_DOCUMENT primary key (qa_document_id)
);

/*==============================================================*/
/* Table: qa_model                                              */
/*==============================================================*/
create table qa_model (
qa_model_id          INT4                 not null,
qa_model_name        TEXT                 not null,
qa_model_desc        TEXT                 null,
qa_model_order       INT4                 not null default 0,
constraint PK_QA_MODEL primary key (qa_model_id)
);

comment on table qa_model is
'Contains Quality Assurance models. A model is simply a hypothetical QA profile which defines the QA requirements
for that profile. It provides a kind of template for assigning default QA Steps etc. There are three simple models
which have been invented to begin with: Small, Medium and Large (referring to project size).';

/*==============================================================*/
/* Table: qa_model_documents                                    */
/*==============================================================*/
create table qa_model_documents (
qa_model_id          INT4                 not null,
qa_document_id       INT4                 not null,
path_to_template     TEXT                 null,
path_to_example      TEXT                 null,
constraint PK_QA_MODEL_DOCUMENTS primary key (qa_model_id, qa_document_id)
);

/*==============================================================*/
/* Table: qa_model_step                                         */
/*==============================================================*/
create table qa_model_step (
qa_model_id          INT4                 not null,
qa_step_id           INT4                 not null,
constraint PK_QA_MODEL_STEP primary key (qa_model_id, qa_step_id)
);

/*==============================================================*/
/* Table: qa_phase                                              */
/*==============================================================*/
create table qa_phase (
qa_phase             TEXT                 not null,
qa_phase_desc        TEXT                 null,
qa_phase_order       INT4                 not null default 0,
constraint PK_QA_PHASE primary key (qa_phase)
);

comment on table qa_phase is
'Contains all of the Quality Assurance Phases available. A QA Phase is a logical grouping of QA Steps. Useful for display and reporting purposes.';

/*==============================================================*/
/* Table: qa_project_approval                                   */
/*==============================================================*/
create table qa_project_approval (
qa_approval_id       INT4                 not null,
project_id           INT4                 not null,
qa_step_id           INT4                 not null,
qa_approval_type_id  INT4                 not null,
approval_status      TEXT                 null 
      constraint CKC_APPROVAL_STATUS_QA_PROJE check (approval_status is null or ( approval_status in ('p','y','n','s') )),
assigned_to_usr      INT4                 null,
assigned_datetime    TIMESTAMP            null,
approval_by_usr      INT4                 null,
approval_datetime    TIMESTAMP            null,
comment              TEXT                 null,
constraint PK_QA_PROJECT_APPROVAL primary key (qa_approval_id)
);

comment on table qa_project_approval is
'Contains Quality Assurance Approvals. A QA Approval record is associated with a given project QA Step and is only created when someone who is permitted to do so seeks to acquire an approval for the given QA Step. NB: QA Approval records are ''read-only'' to the QA application - they are an audit trail of approval activities. A QA user can create as many approvals for the same project, QA Step and Approval Type as they wish - they just keep adding to the approval audit trail.';

comment on column qa_project_approval.assigned_to_usr is
'The user that the approval process is assigned to - by the Project Manager. The approval is then approved by someone allocated to the project, or the project manager or the QA mentor (all are permitted to do it), however if the assigned user does not match the approved-by user, the approval has been explicitly over-ridden.';

comment on column qa_project_approval.approval_by_usr is
'The user who is updating this approval status.';

comment on column qa_project_approval.approval_datetime is
'Time and date that the approval status was changed to the current status.';

comment on column qa_project_approval.comment is
'Used to make brief comments on this approval.';

/*==============================================================*/
/* Table: qa_project_step                                       */
/*==============================================================*/
create table qa_project_step (
project_id           INT4                 not null,
qa_step_id           INT4                 not null,
request_id           INT4                 not null,
responsible_usr      INT4                 null,
responsible_datetime TIMESTAMP            null,
notes                TEXT                 null,
constraint PK_QA_PROJECT_STEP primary key (project_id, qa_step_id)
);

comment on table qa_project_step is
'The Project QA Step table contains the QA Steps defined for a given project. Each step is associated with a WRMS request record, which can be used to attach QA documents etc. and also for final signoff of the task once all the required approvals have been acquired.';

comment on column qa_project_step.project_id is
'The unique ID for a project. This is actually a WRMS request ID of the master WRMS record  created for this project.';

comment on column qa_project_step.qa_step_id is
'This is the QA Step being processed for the given project.';

comment on column qa_project_step.request_id is
'This is the foreign key to the WRMS record for this QA Step. This WRMS record is used during the processing of this QA Step, for attaching docs, making notes etc. in the usual WRMS fashion.';

comment on column qa_project_step.responsible_usr is
'This user is assigned to the QA Step as the person responsible for delivering it and getting it approved. The person is selected from those allocated to the project.';

comment on column qa_project_step.responsible_datetime is
'The datetime that the user responsible for this step was assigned to it.';

/*==============================================================*/
/* Table: qa_project_step_approval                              */
/*==============================================================*/
create table qa_project_step_approval (
project_id           INT4                 not null,
qa_step_id           INT4                 not null,
qa_approval_type_id  INT4                 not null,
last_approval_status TEXT                 null 
      constraint CKC_LAST_APPROVAL_STA_QA_PROJE check (last_approval_status is null or ( last_approval_status in ('p','y','n','s') )),
constraint PK_QA_PROJECT_STEP_APPROVAL primary key (project_id, qa_step_id, qa_approval_type_id)
);

comment on table qa_project_step_approval is
'This contains the list of approval types which are required for a given project QA step. It starts off as the default types as expressed by the ''qa_approval'' table, but may be subsequently modified by the project manager to add or subtract approval types. The presence of one of these records indicates that the given approval type is required for the project QA step. Note that this record also holds a denormalised value of the last approval status registered for this type.';

comment on column qa_project_step_approval.project_id is
'The unique ID for a project. This is actually a WRMS request ID of the master WRMS record  created for this project.';

comment on column qa_project_step_approval.qa_step_id is
'This is the QA Step being processed for the given project.';

/*==============================================================*/
/* Table: qa_step                                               */
/*==============================================================*/
create table qa_step (
qa_step_id           INT4                 not null,
qa_phase             TEXT                 not null,
qa_document_id       INT4                 null,
qa_step_desc         TEXT                 null,
qa_step_notes        TEXT                 null,
qa_step_order        INT4                 not null default 0,
mandatory            BOOL                 not null default false,
enabled              BOOL                 not null default true,
constraint PK_QA_STEP primary key (qa_step_id)
);

comment on table qa_step is
'Contains all of the Quality Assurance Steps that are allowed in a project. A QA Step is a task which needs to be achieved as part of the QA process, and must be QA approved.';

/*==============================================================*/
/* Table: request                                               */
/*==============================================================*/
create table request (
request_id           INT4                 not null,
constraint PK_REQUEST primary key (request_id)
);

/*==============================================================*/
/* Table: request_project                                       */
/*==============================================================*/
create table request_project (
request_id           INT4                 not null,
project_manager      INT4                 null,
qa_mentor            INT4                 null,
qa_model_id          INT4                 null,
qa_phase             TEXT                 null,
constraint PK_REQUEST_PROJECT primary key (request_id)
);

comment on table request_project is
'Contains the master records for projects. Every project has a master WRMS record associated with it, and this table contains pointers to those.';

comment on column request_project.project_manager is
'The user who is the designated project manager for this project.';

comment on column request_project.qa_mentor is
'The user who is designated as the person to help out and guide the project team in quality assurance matters.';

comment on column request_project.qa_model_id is
'The initial choice by the project creator, of the model that the project is closest to in size.';

comment on column request_project.qa_phase is
'The current phase that the project is in. Updated whenever approval action takes place. Can be used as a means of high-level project progress viewing.';

/*==============================================================*/
/* Table: usr                                                   */
/*==============================================================*/
create table usr (
user_no              INT4                 not null,
constraint PK_USR primary key (user_no)
);

alter table qa_approval
   add constraint fk_qa_approval_step foreign key (qa_step_id)
      references qa_step (qa_step_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_approval
   add constraint fk_qa_approval_type foreign key (qa_approval_type_id)
      references qa_approval_type (qa_approval_type_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_model_documents
   add constraint fk_documents_model foreign key (qa_model_id)
      references qa_model (qa_model_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_model_documents
   add constraint fk_model_documents foreign key (qa_document_id)
      references qa_document (qa_document_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_model_step
   add constraint fk_qa_model_step foreign key (qa_step_id)
      references qa_step (qa_step_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_model_step
   add constraint fk_qa_model_step_model foreign key (qa_model_id)
      references qa_model (qa_model_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_project_approval
   add constraint fk_proj_approval_type foreign key (qa_approval_type_id)
      references qa_approval_type (qa_approval_type_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_project_approval
   add constraint fk_proj_qa_approval_usr foreign key (approval_by_usr)
      references usr (user_no)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_project_approval
   add constraint fk_proj_qa_behalf_of_usr foreign key (assigned_to_usr)
      references usr (user_no)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_project_approval
   add constraint fk_project_qa_approval_step foreign key (project_id, qa_step_id)
      references qa_project_step (project_id, qa_step_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_project_step
   add constraint fk_proj_qa_step_project foreign key (project_id)
      references request_project (request_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_project_step
   add constraint fk_qa_proj_step_reqid foreign key (request_id)
      references request (request_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_project_step
   add constraint FK_QA_PROJE_FK_QA_STE_QA_STEP foreign key (qa_step_id)
      references qa_step (qa_step_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_project_step
   add constraint FK_QA_PROJE_REFERENCE_USR foreign key (responsible_usr)
      references usr (user_no)
      on delete restrict on update restrict;

alter table qa_project_step_approval
   add constraint fk_proj_step_appr_type foreign key (qa_approval_type_id)
      references qa_approval_type (qa_approval_type_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_project_step_approval
   add constraint fk_proj_step_approval foreign key (project_id, qa_step_id)
      references qa_project_step (project_id, qa_step_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_step
   add constraint fk_qa_step_document foreign key (qa_document_id)
      references qa_document (qa_document_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table qa_step
   add constraint fk_qa_step_phase foreign key (qa_phase)
      references qa_phase (qa_phase)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table request_project
   add constraint fk_project_phase foreign key (qa_phase)
      references qa_phase (qa_phase)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table request_project
   add constraint fk_qa_mentor_usr foreign key (qa_mentor)
      references usr (user_no)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table request_project
   add constraint fk_qa_project_mgr_usr foreign key (project_manager)
      references usr (user_no)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table request_project
   add constraint fk_req_proj_qa_model foreign key (qa_model_id)
      references qa_model (qa_model_id)
      on delete restrict on update restrict
      deferrable initially deferred;

alter table request_project
   add constraint FK_REQUEST__FK_REQ_PR_REQUEST foreign key (request_id)
      references request (request_id)
      on delete restrict on update restrict
      deferrable initially deferred;

