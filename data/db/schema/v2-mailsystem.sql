--
-- Email aliases
--
CREATE TABLE public.email_alias (
    email_address character varying(256) PRIMARY KEY,
    goto text,
    is_active boolean DEFAULT true,
    account_id uuid REFERENCES public.account (account_id) ON DELETE CASCADE
);

--
-- Email domains that are handled through netric
--
CREATE TABLE public.email_domain (
    domain character varying(256) PRIMARY KEY,
    description text,
    is_active boolean DEFAULT true,
    account_id uuid REFERENCES public.account (account_id) ON DELETE CASCADE
);


--
-- Mailboxes where messages get routed
--
CREATE TABLE public.email_mailbox (
    email_address character varying(256) PRIMARY KEY,
    account_id uuid REFERENCES public.account (account_id) ON DELETE CASCADE
);
