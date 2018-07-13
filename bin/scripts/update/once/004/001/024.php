<?php
/**
 * Create UUIDs from IDs for every object
 */
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

// Do not timeout
$db->query('set statement_timeout to 0');

// First create all UUIDs in the gid field
$sql = "UPDATE objects SET guid = CAST(LPAD(TO_HEX(id), 32, '0') AS UUID) WHERE guid is NULL";


// Loop through all object types and update the primary key
$definitions = $entityDefinitionLoader->getAll();
foreach ($definitions as $def) {
    $tblBase = "objects_" . $def->getObjType();

    // Only alter tables that have not already been updated
    if (!$db->indexExists($tblBase . "_act_id_idx") && $db->tableExists($tblBase ."_act")) {
        // Try deleting the primary key
        try {
            $db->query("ALTER TABLE {$tblBase}_act DROP CONSTRAINT {$tblBase}_act_pkey");
        } catch (\Exception $ex) {
            $log->error("Tried to delete pkey {$tblBase}_act_pkey it did not exist");

            // In some cases, old tables were renamed from the id to the name of the object
            try {
                $db->query("ALTER TABLE {$tblBase}_act DROP CONSTRAINT objects_{$def->getId()}_act_pkey");
            } catch (\Exception $ex) {
                $log->error("Tried to delete pkey objects_{$def->getId()}_act_pkey it did not exist");
            }
        }

        $db->query("CREATE UNIQUE INDEX  {$tblBase}_act_id_idx ON {$tblBase}_act (id)");
        $db->query("ALTER TABLE {$tblBase}_act ADD CONSTRAINT {$tblBase}_act_pkey PRIMARY KEY (guid)");
    }

    // Now update the deleted archive partition
    if (!$db->indexExists($tblBase . "_del_id_idx") && $db->tableExists($tblBase ."_del")) {
        // Try deleting the primary key
        try {
            $db->query("ALTER TABLE {$tblBase}_del DROP CONSTRAINT {$tblBase}_del_pkey");
        } catch (\Exception $ex) {
            $log->error("Tried to delete pkey {$tblBase}_act_pkey it did not exist");

            // In some cases, old tables were renamed from the id to the name of the object
            try {
                $db->query("ALTER TABLE {$tblBase}_del DROP CONSTRAINT objects_{$def->getId()}_del_pkey");
            } catch (\Exception $ex) {
                $log->error("Tried to delete pkey objects_{$def->getId()}_act_pkey it did not exist");
            }
        }

        $db->query("CREATE UNIQUE INDEX  {$tblBase}_del_id_idx ON {$tblBase}_del (id)");
        $db->query("ALTER TABLE {$tblBase}_del ADD CONSTRAINT {$tblBase}_del_pkey PRIMARY KEY (guid)");
    }
}
