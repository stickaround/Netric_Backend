<?php
/**
 * Removes the table fkey contraint in calendar_events table recur_id column
 */
use Netric\Db\DbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(DbFactory::class);

if ($db->tableExists('calendar_events')) {
    $db->query("ALTER TABLE calendar_events DROP CONSTRAINT IF EXISTS calendar_events_recur_fkey");
    
    if ($db->columnExists('calendar_events', 'recur_id')) {
        $db->query("ALTER TABLE calendar_events ALTER COLUMN recur_id TYPE integer");
    }
}
