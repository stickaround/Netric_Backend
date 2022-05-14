-- Add status with the default of 1 which is active
ALTER TABLE account ADD COLUMN status SMALLINT DEFAULT 1 NOT NULL;
-- UPDATE account SET status='1' WHERE status IS NULL;

-- Add billing_next_bill date so we can determine when an account should be billed
ALTER TABLE account ADD COLUMN billing_next_bill timestamp with time zone;

-- Add billing_month_interval date so we can determine how many months pass
-- between billing cycles.
ALTER TABLE account ADD COLUMN billing_month_interval SMALLINT DEFAULT 1 NOT NULL;
-- UPDATE account SET billing_month_interval='1' WHERE billing_month_interval IS NULL;

-- We no longer use this column since we use status now to force
-- users to the billing update page.
ALTER TABLE account DROP COLUMN billing_force_update;