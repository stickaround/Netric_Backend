--
-- PostgreSQL database
--

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;
SET default_tablespace = '';
SET default_with_oids = false;

--
-- TABLE: account
--

CREATE TABLE public.account (
    account_id uuid PRIMARY KEY,
    name character varying(256) UNIQUE,
    database character varying(256),
    ts_started timestamp with time zone,
    server character varying(256),
    version character varying(256),
    active boolean DEFAULT true,
    main_account_contact_id uuid,
    billing_last_billed timestamp with time zone,
    billing_force_update boolean
);


--
-- TABLE: account_module
--

CREATE TABLE public.account_module (
    account_module_id BIGSERIAL PRIMARY KEY,
    account_id uuid NOT NULL,
    name character varying(256) NOT NULL,
    short_title character varying(256) NOT NULL,
    title character varying(512) NOT NULL,
    scope character varying(32),
    settings character varying(128),
    xml_navigation text,
    navigation_data text,
    f_system boolean DEFAULT false,
    user_id uuid,
    team_id uuid,
    sort_order smallint,
    icon text,
    default_route text
);


-- INDEX: account_module_account_id_idx

CREATE INDEX account_module_account_id_idx ON public.account_module USING btree (account_id);

-- INDEX: account_module_team_id_idx

CREATE INDEX account_module_team_id_idx ON public.account_module USING btree (team_id);


-- INDEX: account_module_user_id_idx

CREATE INDEX account_module_user_id_idx ON public.account_module USING btree (user_id);


--
-- TABLE: account_user
--

CREATE TABLE public.account_user (
    account_id uuid NOT NULL,
    email_address character varying(256) NOT NULL,
    username character varying(256)
);


-- CONSTRAINT: account_user account_user_pkey;

ALTER TABLE ONLY public.account_user
    ADD CONSTRAINT account_user_pkey PRIMARY KEY (account_id, email_address);


-- INDEX: account_user_email_address_idx

CREATE INDEX account_user_email_address_idx 
    ON public.account_user USING btree (email_address);


--
-- TABLE: async_device_states
--

CREATE TABLE public.async_device_states (
    id_state bigserial PRIMARY KEY,
    device_id character varying(64),
    uuid character varying(64),
    state_type character varying(64),
    counter integer,
    state_data bytea,
    created_at timestamp with time zone,
    updated_at timestamp with time zone
);


-- INDEX: async_device_states_device_id_uuid_state_type_counter_idx

CREATE UNIQUE INDEX async_device_states_device_id_uuid_state_type_counter_idx 
    ON public.async_device_states USING btree (device_id, uuid, state_type, counter);


-- TABLE: async_users

CREATE TABLE public.async_users (
    username character varying(256) NOT NULL,
    device_id character varying(64) NOT NULL
);


-- CONSTRAINT: async_users async_users_pkey

ALTER TABLE ONLY public.async_users
    ADD CONSTRAINT async_users_pkey PRIMARY KEY (username, device_id);


--
-- TABLE: entity
--

CREATE TABLE public.entity (
    entity_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    uname character varying(256),
    entity_definition_id uuid,
    ts_entered timestamp with time zone,
    ts_updated timestamp with time zone,
    f_deleted boolean DEFAULT false,
    commit_id bigserial,
    field_data jsonb,
    schema_version integer,
    tsv_fulltext tsvector,
    sort_order integer
);


-- INDEX: entity_tsv_fulltext_idx

CREATE INDEX entity_tsv_fulltext_idx ON public.entity USING gin (tsv_fulltext);


-- INDEX: entity_account_id_entity_definition_id_idx

CREATE INDEX entity_account_id_entity_definition_id_idx 
    ON public.entity USING btree (account_id, entity_definition_id);


--
-- TABLE: entity_definition
--

CREATE TABLE public.entity_definition (
    entity_definition_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    name character varying(256),
    title character varying(256),
    revision integer DEFAULT 1,
    f_system boolean DEFAULT false,
    system_definition_hash character varying(32),
    f_table_created boolean DEFAULT false,
    application_id integer,
    capped integer,
    head_commit_id bigint,
    dacl text,
    is_private boolean DEFAULT false,
    store_revisions boolean DEFAULT true,
    recur_rules text,
    inherit_dacl_ref character varying(128),
    parent_field character varying(128),
    uname_settings character varying(256),
    list_title character varying(128),
    icon character varying(128),
    default_activity_level integer,
    def_data jsonb
);


-- INDEX: entity_definition_name_account_id_idx

CREATE INDEX entity_definition_name_account_id_idx 
    ON public.entity_definition USING btree (name, account_id);


-- TABLE: entity_form

CREATE TABLE public.entity_form (
    entity_form_id uuid PRIMARY KEY,
    entity_definition_id uuid,
    account_id uuid NOT NULL,
    team_id uuid,
    user_id uuid,
    scope character varying(128),
    form_layout_xml text
);


-- INDEX: entity_form_entity_definition_id_idx

CREATE INDEX entity_form_entity_definition_id_idx 
    ON public.entity_form USING btree (entity_definition_id);


-- INDEX: entity_form_scope_idx

CREATE INDEX entity_form_scope_idx ON public.entity_form USING btree (scope);


-- INDEX: entity_form_team_id_idx

CREATE INDEX entity_form_team_id_idx ON public.entity_form USING btree (team_id);


-- INDEX: entity_form_user_id_idx

CREATE INDEX entity_form_user_id_idx ON public.entity_form USING btree (user_id);


--
-- TABLE: entity_group
--

CREATE TABLE public.entity_group (
    group_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    name character varying(256),
    entity_definition_id uuid,
    field_id integer,
    parent_id bigint,
    user_id uuid,
    feed_id uuid,
    color character varying(6),
    sort_order smallint,
    f_system boolean DEFAULT false,
    f_closed boolean DEFAULT false,
    commit_id bigint,
    filter_values character varying(256),
    path character varying(256)
);


-- INDEX: entity_group_entity_definition_id_idx

CREATE INDEX entity_group_entity_definition_id_idx 
    ON public.entity_group USING btree (entity_definition_id);


-- INDEX: entity_group_field_id_idx

CREATE INDEX entity_group_field_id_idx ON public.entity_group USING btree (field_id);


-- INDEX: entity_group_parent_id_idx

CREATE INDEX entity_group_parent_id_idx ON public.entity_group USING btree (parent_id);


-- INDEX: entity_group_path_idx

CREATE INDEX entity_group_path_idx ON public.entity_group USING btree (path);


-- INDEX: entity_group_user_id_idx

CREATE INDEX entity_group_user_id_idx ON public.entity_group USING btree (user_id);


--
-- TABLE: entity_moved
--

CREATE TABLE public.entity_moved (
    old_id uuid PRIMARY KEY,
    new_id uuid NOT NULL
);


--
-- TABLE: entity_recurrence
--

CREATE TABLE public.entity_recurrence (
    entity_recurrence_id uuid PRIMARY KEY,
    account_id uuid NOT NULL,
    entity_definition_id uuid NOT NULL,
    type smallint,
    "interval" smallint,
    date_processed_to date,
    date_start date,
    date_end date,
    t_start time with time zone,
    t_end time with time zone,
    all_day boolean DEFAULT false,
    ep_locked integer,
    dayofmonth smallint,
    dayofweekmask boolean[],
    duration integer,
    instance smallint,
    monthofyear smallint,
    parent_entity_id uuid,
    type_id character varying(256),
    f_active boolean DEFAULT true
);


-- INDEX: entity_recurrence_date_processed_to_idx

CREATE INDEX entity_recurrence_date_processed_to_idx 
    ON public.entity_recurrence USING btree (date_processed_to);


--
-- TABLE: entity_revision
--

CREATE TABLE public.entity_revision (
    entity_revision_id bigserial PRIMARY KEY,
    entity_id uuid NOT NULL,
    revision integer,
    ts_updated time with time zone,
    field_data jsonb
);


-- INDEX: entity_revision_entity_id_idx

CREATE INDEX entity_revision_entity_id_idx 
    ON public.entity_revision USING btree (entity_id);


--
-- TABLE: entity_sync_collection
--

CREATE TABLE public.entity_sync_collection (
    entity_sync_collection_id bigserial PRIMARY KEY,
    type integer,
    partner_id integer,
    entity_definition_id uuid,
    object_type character varying(256),
    field_id integer,
    field_name character varying(256),
    ts_last_sync timestamp with time zone,
    conditions text,
    f_initialized boolean DEFAULT false,
    revision bigint,
    last_commit_id bigint
);


--
-- TABLE: entity_sync_commit_head
--

CREATE TABLE public.entity_sync_commit_head (
    type_key character varying(256) PRIMARY KEY,
    head_commit_id bigint NOT NULL
);


--
-- TABLE: entity_sync_export
--

CREATE TABLE public.entity_sync_export (
    entity_sync_export_id bigserial PRIMARY KEY,
    collection_id bigint,
    collection_type smallint,
    commit_id bigint,
    new_commit_id bigint,
    unique_id uuid
);


-- INDEX: entity_sync_export_collection_id_idx

CREATE INDEX entity_sync_export_collection_id_idx 
    ON public.entity_sync_export USING btree (collection_id);


-- INDEX: entity_sync_export_collection_type_commit_id_idx

CREATE INDEX entity_sync_export_collection_type_commit_id_idx 
    ON public.entity_sync_export USING btree (collection_type, commit_id);


-- INDEX: entity_sync_export_collection_type_new_commit_id_idx

CREATE INDEX entity_sync_export_collection_type_new_commit_id_idx 
    ON public.entity_sync_export USING btree (collection_type, new_commit_id);


-- INDEX: entity_sync_export_new_commit_id_new_commit_id_idx

CREATE INDEX entity_sync_export_new_commit_id_new_commit_id_idx 
    ON public.entity_sync_export USING btree (new_commit_id, new_commit_id);


-- INDEX: entity_sync_export_unique_id_idx

CREATE INDEX entity_sync_export_unique_id_idx 
    ON public.entity_sync_export USING btree (unique_id);


--
-- TABLE: entity_sync_import
--

CREATE TABLE public.entity_sync_import (
    entity_sync_import_id bigserial PRIMARY KEY,
    collection_id bigint,
    entity_definition_id uuid,
    object_id uuid,
    revision integer,
    parent_id bigint,
    field_id integer,
    remote_revision integer,
    unique_id character varying(512)
);


-- INDEX: entity_sync_import_entity_definition_id_object_id_idx

CREATE INDEX entity_sync_import_entity_definition_id_object_id_idx 
    ON public.entity_sync_import USING btree (entity_definition_id, object_id);


-- INDEX: entity_sync_import_field_id_unique_id_idx

CREATE INDEX entity_sync_import_field_id_unique_id_idx 
    ON public.entity_sync_import USING btree (field_id, unique_id);


-- INDEX: entity_sync_import_parent_id_idx

CREATE INDEX entity_sync_import_parent_id_idx 
    ON public.entity_sync_import USING btree (parent_id);


--
-- TABLE: entity_sync_partner
--

CREATE TABLE public.entity_sync_partner (
    entity_sync_partner_id bigserial PRIMARY KEY,
    pid character varying(256),
    owner_id uuid,
    ts_last_sync timestamp with time zone
);


-- INDEX: entity_sync_partner_pid_idx

CREATE INDEX entity_sync_partner_pid_idx ON public.entity_sync_partner USING btree (pid);


--
-- TABLE: entity_view
--

CREATE TABLE public.entity_view (
    entity_view_id bigserial PRIMARY KEY,
    name character varying(256) NOT NULL,
    scope character varying(16),
    description text,
    filter_key text,
    f_default boolean DEFAULT false,
    user_id uuid,
    team_id uuid,
    entity_definition_id uuid NOT NULL,
    report_id uuid,
    owner_id uuid,
    conditions_data text,
    order_by_data text,
    table_columns_data text,
    group_first_order_by boolean DEFAULT false
);


--
-- INDEX: entity_view_entity_definition_id_idx
--

CREATE INDEX entity_view_entity_definition_id_idx 
    ON public.entity_view USING btree (entity_definition_id);


-- INDEX: entity_view_owner_id_idx

CREATE INDEX entity_view_owner_id_idx ON public.entity_view USING btree (owner_id);


--
-- SEQUENCE: object_commit_seq
-- 
-- Used just to increment our way through save/update actions to entity so
-- that we can do differential operations
--

CREATE SEQUENCE public.entity_commit_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- TABLE: settings
--

CREATE TABLE public.settings (
    id bigserial PRIMARY KEY,
    name character varying(256),
    value text,
    user_id uuid,
    account_id uuid
);


-- INDEX: settings_user_id_idx

CREATE INDEX settings_user_id_idx ON public.settings USING btree (user_id);


--
-- TABLE: public.worker_process_lock
--
-- This table is used to lock distributed processes.
--

CREATE TABLE public.worker_process_lock (
    id bigserial PRIMARY KEY,
    process_name character varying(256),
    ts_entered timestamp with time zone
);


-- INDEX: worker_process_lock_process_name_idx
-- process_name will be used in almost all queries

CREATE UNIQUE INDEX worker_process_lock_process_name_idx 
    ON public.worker_process_lock USING btree (process_name);


-- INDEX: worker_process_lock_ts_entered_idx
-- ts_entered is the primary sort used in queries

CREATE INDEX worker_process_lock_ts_entered_idx 
    ON public.worker_process_lock USING btree (ts_entered);
