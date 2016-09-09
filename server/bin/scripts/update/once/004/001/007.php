<?php
/**
 * Removes the table fkey contraint in calendar_events table recur_id column
 */

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$dbh = $serviceManager->get("Netric/Db/Db");

$dbh->query("ALTER TABLE calendar_events DROP CONSTRAINT IF EXISTS calendar_events_recur_fkey");
$dbh->query("ALTER TABLE calendar_events ALTER COLUMN recur_id TYPE integer");