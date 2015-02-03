-- Create table to store the last commit for different kinds of commits
CREATE TABLE object_sync_commit_heads 
(
	type_key character varying(256),
	head_commit_id bigint NOT NULL,
	CONSTRAINT object_sync_commit_heads_pkey PRIMARY KEY (type_key)
);

-- Add last_commit_id to each partner collection so we know
-- where to puckup next upon next sync
ALTER TABLE object_sync_partner_collections ADD COLUMN last_commit_id bigint;

-- Add a type column so we know what kind of collection to load
ALTER TABLE object_sync_partner_collections ADD COLUMN type character varying(16);

-- Copy the last commit from app_object_types from previous revisions
INSERT INTO object_sync_commit_heads(type_key, head_commit_id)
SELECT 'entities/'||name, head_commit_id FROM app_object_types  
WHERE head_commit_id is not NULL;