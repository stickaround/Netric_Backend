<?php

/**
 * Fix an issue where notes user_id did not get copied to owner_id
 */

use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\EntityFactoryFactory;

$account = $this->getAccount();
$user = $account->getAuthenticatedUser();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityFactory = $serviceManager->get(EntityFactoryFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);


$result = $db->query("select field_data, entity_id from entity where entity_definition_id='00000000-0000-0000-0000-000000000028' and field_data->>'owner_id' IS NULL");
for ($i = 0; $i < $result->rowCount(); $i++) {
    $row = $result->fetch();
    $data = json_decode($row['field_data'], true);

    // Copy user_id to owner_id, or move to sky UUID if user_id is empty
    $data['owner_id'] = '00000000-0000-0000-0000-000006b40500';
    if (isset($data['user_id']) && !empty($data['user_id'])) {
        $data['owner_id'] = $data['user_id'];
    }

    $db->query(
        "UPDATE entity SET field_data=:field_data WHERE entity_id=:entity_id",
        ['field_data' => json_encode($row['field_data']), 'entity_id' => $row['entity_id']]
    );
}
