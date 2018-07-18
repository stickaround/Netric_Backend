<?php
/**
 * We added f_seen to all objects, but it defaulted to false when null which is super annoying
 * since old lists all are highlighted as unseen. This update will go through and update every entry
 * that was previously saved in the objects_* tables to f_seen=true where null. Entities will do
 * this automatically for all future saves due to the field default.
 */
use Netric\Db\DbFactory;
use Netric\Entity\EntityLoaderFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(DbFactory::class);
$loader = $serviceManager->get(EntityLoaderFactory::class);
$log = $account->getApplication()->getLog();

$projectMemberships = [];
if ($db->columnExists('app_object_types', 'object_table')) {
    $result = $db->query("SELECT id, name, object_table from app_object_types");
    for ($i = 0; $i < $db->getNumRows($result); $i++) {
        // Get the result row
        $row = $db->getRow($result, $i);
    
        $table = ($row['object_table']) ? $row['object_table'] : 'objects_' . $row['name'];
    
        if ($db->columnExists($table, 'f_seen')) {
            $ret = $db->query("UPDATE $table SET f_seen=true WHERE f_seen IS NULL");
            if (!$ret) {
                $log->error("Update 004.001.013 failed to update table: " . $db->getLastError());
            }
        }
    }
}