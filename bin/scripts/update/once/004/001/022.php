<?php
/**
 * Copy over all settings from system_registry to new settings table
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityLoaderFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);

// Check if legacy system_registry table exists
if ($db->tableExists('system_registry')) {
    // And if new settings table is empty
    $result = $db->query('SELECT count(*) as cnt FROM settings');
    $row = $result->fetch();
    if ($row['cnt'] == 0) {
        // Then copy system_registry into settings
        $sql = "INSERT INTO settings(name, value) SELECT key_name, key_value from system_registry";
        $result = $db->query($sql);
    }
}
