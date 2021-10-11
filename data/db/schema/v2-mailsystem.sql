CREATE TABLE public.email_alias (
    address character varying(256) PRIMARY KEY,
    goto text,
    is_active boolean DEFAULT true,
    account_id uuid NOT NULL
);

--
-- Email domains that are handled through netric
--

CREATE TABLE public.email_domain (
    domain character varying(256) PRIMARY KEY,
    description text,
    is_active boolean DEFAULT true,
    account_id uuid NOT NULL
);


--
-- Delivery dropboxes should all be wildcard
--

CREATE TABLE public.email_user (
    id bigint NOT NULL,
    email_address character varying(256),
    maildir character varying(128),
    password character varying(128),
    account_id bigint
);
