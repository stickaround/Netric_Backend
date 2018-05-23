<?php
/**
 * Removes the table fkey contraint in calendar_events table recur_id column
 */
use Netric\Db\DbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(DbFactory::class);

$db->query("ALTER TABLE calendar_events DROP CONSTRAINT IF EXISTS calendar_events_recur_fkey");
$db->query("ALTER TABLE calendar_events ALTER COLUMN recur_id TYPE integer");
