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
    $result = $db->query('SELECT key_name, key_val, user_id from system_registry');
    foreach ($result->fetchAll() as $row) {
        $settingResult = $db->query(
            'SELECT id FROM settings WHERE name=:key_name AND user_id=:user_id',
            ['key_name'=>$row['key_name'], 'user_id'=>$row['user_id']]
        );
        if ($settingResult->rowCount() == 0) {
            // Then copy system_registry into settings
            $db->insert('settings', [
                'name' => $row['key_name'],
                'value' => $row['key_val'],
                'user_id' => $row['user_id'],
            ]);
        }
    }
}
