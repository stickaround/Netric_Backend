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


// Make sure system users have the right uname and guid
$users = require(__DIR__ . "/../../../../../../data/account/users.php");
foreach ($users as $userData) {
    $query = new EntityQuery("user");
    $query->where('name')->equals($userData['name']);
    $index = $this->getServiceManager()->get(IndexFactory::class);
    $res = $index->executeQuery($query);
    if ($res->getTotalNum() > 0) {
        $userEntity = $res->getEntity(0);
        $userEntity->setvalue('uname', $userData['uname']);
        $userEntity->setvalue('guid', $userData['guid']);
        $entityLoader->save($userEntity);
    }
}