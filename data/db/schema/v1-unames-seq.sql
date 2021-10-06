--
-- SEQUENCE: entity_uname_seq
-- 
-- Used to provide unique auto-generated unames for entities
-- like T-123456 for a task.
--
CREATE SEQUENCE IF NOT EXISTS public.entity_uname_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;

-- Backfill any empty entities with a unique ID
UPDATE entity SET uname=nextval('entity_uname_seq') 
    WHERE uname IS NULL OR uname='';

-- INDEX: entity_account_id_entity_definition_id_idx
CREATE UNIQUE INDEX IF NOT EXISTS entity_uname_idx 
    ON public.entity USING btree (uname, account_id, f_deleted);