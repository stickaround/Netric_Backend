<?php
/**
 * This is an update to EntitySync which changes revision in
 * object_sync_import from entity revision to entity commit_id
 */
use Netric\Entity\EntityLoaderFactory;
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);

$sql = "SELECT osi.id, osi.object_id, osi.revision, osc.object_type
        FROM object_sync_import as osi inner join object_sync_partner_collections as osc ON (
          osi.collection_id=osc.id
        )
        WHERE
          osi.object_id IS NOT NULL AND
          osi.revision IS NOT NULL AND
          osc.object_type IS NOT NULL AND
          (osc.field_name IS NULL OR osc.field_name='')";

$result = $db->query($sql);

foreach ($result->fetchAll() as $row) {
    $entity = $entityLoader->get($row['object_type'], $row['object_id']);

    if ($entity && $entity->getValue("commit_id") && $entity->getValue('commit_id') != $row['revision']) {
        $db->update("object_sync_import", ["revision" => $commitId], ["id" => $row['id']]);
    } else {
        // At some point an entity was imported but it looks like it may be stale now.
        $db->delete("object_sync_import", ["id" => $row['id']]);
    }
}
