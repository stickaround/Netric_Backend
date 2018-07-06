<?php
/**
 * Update default users with static guid and uname
 */
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);

// Update the uname of all users
$query = new EntityQuery("user");
$index = $serviceManager->get(IndexFactory::class);
$res = $index->executeQuery($query);
for ($i = 0; $i < $res->getTotalNum(); $i++) {
    $userEntity = $res->getEntity($i);
    // Reset uname so that on save name is copied to uname
    $userEntity->setValue('uname', '');
    $entityLoader->save($userEntity);
}

// Make sure system users have the right uname and guid
$users = require(__DIR__ . "/../../../../../../data/account/users.php");
foreach ($users as $userData) {
    $query = new EntityQuery("user");
    $query->where('name')->equals($userData['name']);
    $index = $serviceManager->get(IndexFactory::class);
    $res = $index->executeQuery($query);
    if ($res->getTotalNum() > 0) {
        $userEntity = $res->getEntity(0);
        $userEntity->setvalue('guid', $userData['guid']);
        $userEntity->setValue('uname', '');
        $entityLoader->save($userEntity);
    }
}
