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

// Wrap this into a transaction so that we can extend the statement timeout (it takes a while)
$db->beginTransaction();
// Do not timeout for this long query
$db->query('set statement_timeout to 0');
// First create all UUIDs in the gid field
$sql = "UPDATE objects SET guid = CAST(LPAD(TO_HEX(id), 32, '0') AS UUID) WHERE guid is NULL";
$db->query($sql);
// Commit the transaction
$db->commitTransaction();

// Loop through all object types and update the primary key
$definitions = $entityDefinitionLoader->getAll();
foreach ($definitions as $def) {
    $tblBase = "objects_" . $def->getObjType();

    // Only alter tables that have not already been updated
    if (!$db->indexExists($tblBase . "_act_id_idx") && $db->tableExists($tblBase ."_act")) {
        $db->query("ALTER TABLE {$tblBase}_act DROP CONSTRAINT {$tblBase}_act_pkey");
        $db->query("CREATE UNIQUE INDEX  {$tblBase}_act_id_idx ON {$tblBase}_act (id)");
        $db->query("ALTER TABLE {$tblBase}_act ADD CONSTRAINT {$tblBase}_act_pkey PRIMARY KEY (guid)");
    }

    // Now update the deleted archive partition
    if (!$db->indexExists($tblBase . "_del_id_idx") && $db->tableExists($tblBase ."_del")) {
        $db->query("ALTER TABLE {$tblBase}_del DROP CONSTRAINT {$tblBase}_del_pkey");
        $db->query("CREATE UNIQUE INDEX  {$tblBase}_del_id_idx ON {$tblBase}_del (id)");
        $db->query("ALTER TABLE {$tblBase}_del ADD CONSTRAINT {$tblBase}_del_pkey PRIMARY KEY (guid)");
    }
}
