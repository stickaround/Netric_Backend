

--
-- Name: account; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.account (
    account_id uuid NOT NULL,
    name character varying(256),
    database character varying(256),
    ts_started timestamp with time zone,
    server character varying(256),
    version character varying(256),
    active boolean DEFAULT true
);


--
-- Name: account_user; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.account_user (
    account_id uuid NOT NULL,
    email_address character varying(256) NOT NULL,
    username character varying(256)
);



--
-- Name: account_module; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.account_module (
    account_module_id bigint NOT NULL,
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

--
-- Name: account_module_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.account_module_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: account_module_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.account_module_id_seq OWNED BY public.applications.id;


--
-- Name: async_device_states; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.async_device_states (
    id_state bigint NOT NULL,
    device_id character varying(64),
    uuid character varying(64),
    state_type character varying(64),
    counter integer,
    state_data bytea,
    created_at timestamp with time zone,
    updated_at timestamp with time zone
);


ALTER TABLE public.async_device_states OWNER TO vagrant;

--
-- Name: async_device_states_id_state_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.async_device_states_id_state_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.async_device_states_id_state_seq OWNER TO vagrant;

--
-- Name: async_device_states_id_state_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.async_device_states_id_state_seq OWNED BY public.async_device_states.id_state;


--
-- Name: async_users; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.async_users (
    username character varying(256) NOT NULL,
    device_id character varying(64) NOT NULL
);


ALTER TABLE public.async_users OWNER TO vagrant;

--
-- Name: entity; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity (
    entity_id uuid NOT NULL,
    account_id uuid NOT NULL,
    uname character varying(256),
    entity_definition_id uuid,
    ts_entered timestamp with time zone,
    ts_updated timestamp with time zone,
    f_deleted boolean DEFAULT false,
    commit_id bigint,
    field_data jsonb,
    schema_version integer,
    tsv_fulltext tsvector
);


ALTER TABLE public.entity OWNER TO vagrant;

--
-- Name: entity_commit_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.entity_commit_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.entity_commit_seq OWNER TO vagrant;

--
-- Name: entity_definition; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_definition (
    entity_definition_id uuid NOT NULL,
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


ALTER TABLE public.entity_definition OWNER TO vagrant;

--
-- Name: entity_form; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_form (
    entity_form_id uuid NOT NULL,
    entity_definition_id uuid,
    account_id uuid NOT NULL,
    team_id uuid,
    user_id uuid,
    scope character varying(128),
    form_layout_xml text
);


ALTER TABLE public.entity_form OWNER TO vagrant;

--
-- Name: entity_group; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_group (
    group_id uuid NOT NULL,
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


ALTER TABLE public.entity_group OWNER TO vagrant;

--
-- Name: entity_moved; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_moved (
    old_id uuid NOT NULL,
    new_id uuid NOT NULL
);


ALTER TABLE public.entity_moved OWNER TO vagrant;

--
-- Name: entity_recurrence; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_recurrence (
    entity_recurrence_id uuid NOT NULL,
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


ALTER TABLE public.entity_recurrence OWNER TO vagrant;

--
-- Name: entity_revision; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_revision (
    entity_revision_id bigint NOT NULL,
    entity_id uuid NOT NULL,
    revision integer,
    ts_updated time with time zone,
    field_data jsonb
);


ALTER TABLE public.entity_revision OWNER TO vagrant;

--
-- Name: entity_revision_entity_revision_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.entity_revision_entity_revision_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.entity_revision_entity_revision_id_seq OWNER TO vagrant;

--
-- Name: entity_revision_entity_revision_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.entity_revision_entity_revision_id_seq OWNED BY public.entity_revision.entity_revision_id;


--
-- Name: entity_sync_collection; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_sync_collection (
    entity_sync_collection_id bigint NOT NULL,
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


ALTER TABLE public.entity_sync_collection OWNER TO vagrant;

--
-- Name: entity_sync_collection_entity_sync_collection_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.entity_sync_collection_entity_sync_collection_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.entity_sync_collection_entity_sync_collection_id_seq OWNER TO vagrant;

--
-- Name: entity_sync_collection_entity_sync_collection_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.entity_sync_collection_entity_sync_collection_id_seq OWNED BY public.entity_sync_collection.entity_sync_collection_id;


--
-- Name: entity_sync_commit_head; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_sync_commit_head (
    type_key character varying(256) NOT NULL,
    head_commit_id bigint NOT NULL
);


ALTER TABLE public.entity_sync_commit_head OWNER TO vagrant;

--
-- Name: entity_sync_export; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_sync_export (
    entity_sync_export_id bigint NOT NULL,
    collection_id bigint,
    collection_type smallint,
    commit_id bigint,
    new_commit_id bigint,
    unique_id uuid
);


ALTER TABLE public.entity_sync_export OWNER TO vagrant;

--
-- Name: entity_sync_export_entity_sync_export_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.entity_sync_export_entity_sync_export_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.entity_sync_export_entity_sync_export_id_seq OWNER TO vagrant;

--
-- Name: entity_sync_export_entity_sync_export_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.entity_sync_export_entity_sync_export_id_seq OWNED BY public.entity_sync_export.entity_sync_export_id;


--
-- Name: entity_sync_import; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_sync_import (
    entity_sync_import_id bigint NOT NULL,
    collection_id bigint,
    entity_definition_id uuid,
    object_id uuid,
    revision integer,
    parent_id bigint,
    field_id integer,
    remote_revision integer,
    unique_id character varying(512)
);


ALTER TABLE public.entity_sync_import OWNER TO vagrant;

--
-- Name: entity_sync_import_entity_sync_import_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.entity_sync_import_entity_sync_import_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.entity_sync_import_entity_sync_import_id_seq OWNER TO vagrant;

--
-- Name: entity_sync_import_entity_sync_import_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.entity_sync_import_entity_sync_import_id_seq OWNED BY public.entity_sync_import.entity_sync_import_id;


--
-- Name: entity_sync_partner; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_sync_partner (
    entity_sync_partner_id bigint NOT NULL,
    pid character varying(256),
    owner_id uuid,
    ts_last_sync timestamp with time zone
);


ALTER TABLE public.entity_sync_partner OWNER TO vagrant;

--
-- Name: entity_sync_partner_entity_sync_partner_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.entity_sync_partner_entity_sync_partner_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.entity_sync_partner_entity_sync_partner_id_seq OWNER TO vagrant;

--
-- Name: entity_sync_partner_entity_sync_partner_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.entity_sync_partner_entity_sync_partner_id_seq OWNED BY public.entity_sync_partner.entity_sync_partner_id;


--
-- Name: entity_view; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.entity_view (
    entity_view_id bigint NOT NULL,
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


ALTER TABLE public.entity_view OWNER TO vagrant;

--
-- Name: entity_view_entity_view_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.entity_view_entity_view_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.entity_view_entity_view_id_seq OWNER TO vagrant;

--
-- Name: entity_view_entity_view_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.entity_view_entity_view_id_seq OWNED BY public.entity_view.entity_view_id;


--
-- Name: object_recurrence_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.object_recurrence_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.object_recurrence_id_seq OWNER TO vagrant;

--
-- Name: settings; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.settings (
    id bigint NOT NULL,
    name character varying(256),
    value text,
    user_id uuid,
    account_id uuid
);


ALTER TABLE public.settings OWNER TO vagrant;

--
-- Name: settings_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.settings_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.settings_id_seq OWNER TO vagrant;

--
-- Name: settings_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.settings_id_seq OWNED BY public.settings.id;


--
-- Name: unit_test_schema; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.unit_test_schema (
    id bigint NOT NULL,
    name character varying(128),
    value integer,
    some_unique character varying(128)
);


ALTER TABLE public.unit_test_schema OWNER TO vagrant;

--
-- Name: unit_test_schema_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.unit_test_schema_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.unit_test_schema_id_seq OWNER TO vagrant;

--
-- Name: unit_test_schema_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.unit_test_schema_id_seq OWNED BY public.unit_test_schema.id;


--
-- Name: worker_process_lock; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.worker_process_lock (
    id bigint NOT NULL,
    process_name character varying(256),
    ts_entered timestamp with time zone
);


ALTER TABLE public.worker_process_lock OWNER TO vagrant;

--
-- Name: worker_process_lock_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.worker_process_lock_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.worker_process_lock_id_seq OWNER TO vagrant;

--
-- Name: worker_process_lock_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.worker_process_lock_id_seq OWNED BY public.worker_process_lock.id;


--
-- Name: workflow_instances; Type: TABLE; Schema: public; Owner: vagrant
--

CREATE TABLE public.workflow_instances (
    id bigint NOT NULL,
    entity_definition_id uuid NOT NULL,
    object_type character varying(128),
    object_uid bigint NOT NULL,
    workflow_id integer,
    ts_started timestamp with time zone,
    ts_completed timestamp with time zone,
    f_completed boolean DEFAULT false
);


ALTER TABLE public.workflow_instances OWNER TO vagrant;

--
-- Name: workflow_instances_id_seq; Type: SEQUENCE; Schema: public; Owner: vagrant
--

CREATE SEQUENCE public.workflow_instances_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.workflow_instances_id_seq OWNER TO vagrant;

--
-- Name: workflow_instances_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: vagrant
--

ALTER SEQUENCE public.workflow_instances_id_seq OWNED BY public.workflow_instances.id;


--
-- Name: applications id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.applications ALTER COLUMN id SET DEFAULT nextval('public.account_module_id_seq'::regclass);


--
-- Name: async_device_states id_state; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.async_device_states ALTER COLUMN id_state SET DEFAULT nextval('public.async_device_states_id_state_seq'::regclass);


--
-- Name: entity_revision entity_revision_id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_revision ALTER COLUMN entity_revision_id SET DEFAULT nextval('public.entity_revision_entity_revision_id_seq'::regclass);


--
-- Name: entity_sync_collection entity_sync_collection_id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_sync_collection ALTER COLUMN entity_sync_collection_id SET DEFAULT nextval('public.entity_sync_collection_entity_sync_collection_id_seq'::regclass);


--
-- Name: entity_sync_export entity_sync_export_id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_sync_export ALTER COLUMN entity_sync_export_id SET DEFAULT nextval('public.entity_sync_export_entity_sync_export_id_seq'::regclass);


--
-- Name: entity_sync_import entity_sync_import_id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_sync_import ALTER COLUMN entity_sync_import_id SET DEFAULT nextval('public.entity_sync_import_entity_sync_import_id_seq'::regclass);


--
-- Name: entity_sync_partner entity_sync_partner_id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_sync_partner ALTER COLUMN entity_sync_partner_id SET DEFAULT nextval('public.entity_sync_partner_entity_sync_partner_id_seq'::regclass);


--
-- Name: entity_view entity_view_id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_view ALTER COLUMN entity_view_id SET DEFAULT nextval('public.entity_view_entity_view_id_seq'::regclass);


--
-- Name: settings id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.settings ALTER COLUMN id SET DEFAULT nextval('public.settings_id_seq'::regclass);


--
-- Name: unit_test_schema id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.unit_test_schema ALTER COLUMN id SET DEFAULT nextval('public.unit_test_schema_id_seq'::regclass);


--
-- Name: worker_process_lock id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.worker_process_lock ALTER COLUMN id SET DEFAULT nextval('public.worker_process_lock_id_seq'::regclass);


--
-- Name: workflow_instances id; Type: DEFAULT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.workflow_instances ALTER COLUMN id SET DEFAULT nextval('public.workflow_instances_id_seq'::regclass);


--
-- Name: account account_name_key; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.account
    ADD CONSTRAINT account_name_key UNIQUE (name);


--
-- Name: account account_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.account
    ADD CONSTRAINT account_pkey PRIMARY KEY (account_id);


--
-- Name: account_user account_user_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.account_user
    ADD CONSTRAINT account_user_pkey PRIMARY KEY (account_id, email_address);


--
-- Name: applications account_module_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.applications
    ADD CONSTRAINT account_module_pkey PRIMARY KEY (id);


--
-- Name: async_device_states async_device_states_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.async_device_states
    ADD CONSTRAINT async_device_states_pkey PRIMARY KEY (id_state);


--
-- Name: async_users async_users_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.async_users
    ADD CONSTRAINT async_users_pkey PRIMARY KEY (username, device_id);


--
-- Name: entity_definition entity_definition_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_definition
    ADD CONSTRAINT entity_definition_pkey PRIMARY KEY (entity_definition_id);


--
-- Name: entity_form entity_form_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_form
    ADD CONSTRAINT entity_form_pkey PRIMARY KEY (entity_form_id);


--
-- Name: entity_group entity_group_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_group
    ADD CONSTRAINT entity_group_pkey PRIMARY KEY (group_id);


--
-- Name: entity_moved entity_moved_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_moved
    ADD CONSTRAINT entity_moved_pkey PRIMARY KEY (old_id);


--
-- Name: entity entity_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity
    ADD CONSTRAINT entity_pkey PRIMARY KEY (entity_id);


--
-- Name: entity_recurrence entity_recurrence_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_recurrence
    ADD CONSTRAINT entity_recurrence_pkey PRIMARY KEY (entity_recurrence_id);


--
-- Name: entity_revision entity_revision_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_revision
    ADD CONSTRAINT entity_revision_pkey PRIMARY KEY (entity_revision_id);


--
-- Name: entity_sync_collection entity_sync_collection_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_sync_collection
    ADD CONSTRAINT entity_sync_collection_pkey PRIMARY KEY (entity_sync_collection_id);


--
-- Name: entity_sync_commit_head entity_sync_commit_head_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_sync_commit_head
    ADD CONSTRAINT entity_sync_commit_head_pkey PRIMARY KEY (type_key);


--
-- Name: entity_sync_import entity_sync_import_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_sync_import
    ADD CONSTRAINT entity_sync_import_pkey PRIMARY KEY (entity_sync_import_id);


--
-- Name: entity_sync_partner entity_sync_partner_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_sync_partner
    ADD CONSTRAINT entity_sync_partner_pkey PRIMARY KEY (entity_sync_partner_id);


--
-- Name: entity_view entity_view_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.entity_view
    ADD CONSTRAINT entity_view_pkey PRIMARY KEY (entity_view_id);


--
-- Name: settings settings_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.settings
    ADD CONSTRAINT settings_pkey PRIMARY KEY (id);


--
-- Name: unit_test_schema unit_test_schema_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.unit_test_schema
    ADD CONSTRAINT unit_test_schema_pkey PRIMARY KEY (id);


--
-- Name: worker_process_lock worker_process_lock_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.worker_process_lock
    ADD CONSTRAINT worker_process_lock_pkey PRIMARY KEY (id);


--
-- Name: workflow_instances workflow_instances_pkey; Type: CONSTRAINT; Schema: public; Owner: vagrant
--

ALTER TABLE ONLY public.workflow_instances
    ADD CONSTRAINT workflow_instances_pkey PRIMARY KEY (id);


--
-- Name: account_user_email_address_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX account_user_email_address_idx ON public.account_user USING btree (email_address);


--
-- Name: account_module_team_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX account_module_team_id_idx ON public.applications USING btree (team_id);


--
-- Name: account_module_user_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX account_module_user_id_idx ON public.applications USING btree (user_id);


--
-- Name: async_device_states_device_id_uuid_state_type_counter_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE UNIQUE INDEX async_device_states_device_id_uuid_state_type_counter_idx ON public.async_device_states USING btree (device_id, uuid, state_type, counter);


--
-- Name: entity_account_id_entity_definition_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_account_id_entity_definition_id_idx ON public.entity USING btree (account_id, entity_definition_id);


--
-- Name: entity_definition_name_account_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_definition_name_account_id_idx ON public.entity_definition USING btree (name, account_id);


--
-- Name: entity_form_entity_definition_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_form_entity_definition_id_idx ON public.entity_form USING btree (entity_definition_id);


--
-- Name: entity_form_scope_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_form_scope_idx ON public.entity_form USING btree (scope);


--
-- Name: entity_form_team_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_form_team_id_idx ON public.entity_form USING btree (team_id);


--
-- Name: entity_form_user_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_form_user_id_idx ON public.entity_form USING btree (user_id);


--
-- Name: entity_group_entity_definition_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_group_entity_definition_id_idx ON public.entity_group USING btree (entity_definition_id);


--
-- Name: entity_group_field_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_group_field_id_idx ON public.entity_group USING btree (field_id);


--
-- Name: entity_group_parent_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_group_parent_id_idx ON public.entity_group USING btree (parent_id);


--
-- Name: entity_group_path_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_group_path_idx ON public.entity_group USING btree (path);


--
-- Name: entity_group_user_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_group_user_id_idx ON public.entity_group USING btree (user_id);


--
-- Name: entity_recurrence_date_processed_to_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_recurrence_date_processed_to_idx ON public.entity_recurrence USING btree (date_processed_to);


--
-- Name: entity_revision_entity_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_revision_entity_id_idx ON public.entity_revision USING btree (entity_id);


--
-- Name: entity_sync_export_collection_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_sync_export_collection_id_idx ON public.entity_sync_export USING btree (collection_id);


--
-- Name: entity_sync_export_collection_type_commit_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_sync_export_collection_type_commit_id_idx ON public.entity_sync_export USING btree (collection_type, commit_id);


--
-- Name: entity_sync_export_collection_type_new_commit_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_sync_export_collection_type_new_commit_id_idx ON public.entity_sync_export USING btree (collection_type, new_commit_id);


--
-- Name: entity_sync_export_new_commit_id_new_commit_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_sync_export_new_commit_id_new_commit_id_idx ON public.entity_sync_export USING btree (new_commit_id, new_commit_id);


--
-- Name: entity_sync_export_unique_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_sync_export_unique_id_idx ON public.entity_sync_export USING btree (unique_id);


--
-- Name: entity_sync_import_entity_definition_id_object_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_sync_import_entity_definition_id_object_id_idx ON public.entity_sync_import USING btree (entity_definition_id, object_id);


--
-- Name: entity_sync_import_field_id_unique_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_sync_import_field_id_unique_id_idx ON public.entity_sync_import USING btree (field_id, unique_id);


--
-- Name: entity_sync_import_parent_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_sync_import_parent_id_idx ON public.entity_sync_import USING btree (parent_id);


--
-- Name: entity_sync_partner_pid_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_sync_partner_pid_idx ON public.entity_sync_partner USING btree (pid);


--
-- Name: entity_tsv_fulltext_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_tsv_fulltext_idx ON public.entity USING gin (tsv_fulltext);


--
-- Name: entity_view_entity_definition_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_view_entity_definition_id_idx ON public.entity_view USING btree (entity_definition_id);


--
-- Name: entity_view_owner_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX entity_view_owner_id_idx ON public.entity_view USING btree (owner_id);


--
-- Name: settings_user_id_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX settings_user_id_idx ON public.settings USING btree (user_id);


--
-- Name: unit_test_schema_name_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX unit_test_schema_name_idx ON public.unit_test_schema USING btree (name);


--
-- Name: worker_process_lock_process_name_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE UNIQUE INDEX worker_process_lock_process_name_idx ON public.worker_process_lock USING btree (process_name);


--
-- Name: worker_process_lock_ts_entered_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX worker_process_lock_ts_entered_idx ON public.worker_process_lock USING btree (ts_entered);


--
-- Name: workflow_instances_object_type_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX workflow_instances_object_type_idx ON public.workflow_instances USING btree (object_type);


--
-- Name: workflow_instances_object_uid_idx; Type: INDEX; Schema: public; Owner: vagrant
--

CREATE INDEX workflow_instances_object_uid_idx ON public.workflow_instances USING btree (object_uid);


--
-- PostgreSQL database dump complete
--

