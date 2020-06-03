<?php
/**
 * Update all user-defined browser views to populate owner_id field if it still empty
 */
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;
use Netric\Entity\EntityLoaderFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);

$db->beginTransaction();

// Do not timeout for this long query
$db->query('set statement_timeout to 0');

$result = $db->query('SELECT id, user_id, owner_id FROM app_object_views');

// Commit the transaction
$db->commitTransaction();

foreach ($result->fetchAll() as $rowData) {
    // If owner_id is still empty and we have user_id then let's populate the owner_id with the user's guid
    if (!$rowData['owner_id'] && $rowData['user_id']) {
      $userEntity = $entityLoader->get('user', $rowData['user_id']);

      if ($userEntity) {
        $db->update('app_object_views', ['owner_id' => $userEntity->getGuid()], ['id' => $rowData['id']]);
      }
    }
}

