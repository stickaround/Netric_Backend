-- Add the org_name column
ALTER TABLE account ADD COLUMN org_name CHARACTER VARYING(256);

-- Now set some defaults
UPDATE account SET org_name=name WHERE org_name IS NULL;