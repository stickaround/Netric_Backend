<?php

/**
 * Scan the moved entities and update its references
 */
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);

$sql = "SELECT * FROM objects_moved";
$result = $db->query($sql);
$rows = $result->fetchAll();

foreach ($rows as $row) {
    $sql = "SELECT * FROM app_object_types WHERE id=:id";
    $result = $db->query($sql, ["id" => $row["object_type_id"]]);

    if ($result->rowCount()) {
        $objectTypeData = $result->fetch();

        $objTypeDef = $entityDefinitionLoader->get($objectTypeData["name"]);
        $entityDataMapper->updateOldReferences($objTypeDef, $row["object_id"], $row["moved_to"]);
    }
}



