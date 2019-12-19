<?php
/**
 * Update default users with static guid and uname
 */
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\DataMapper\DataMapperFactory as EntityDefinitionDataMapperFactory;
use Netric\EntityDefinition\ObjectTypes;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$entityLoader = $serviceManager->get(EntityLoaderFactory::class);
$db = $serviceManager->get(RelationalDbFactory::class);
$entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
$entityDefinitionDataMapper = $serviceManager->get(EntityDefinitionDataMapperFactory::class);

// Make sure the user entity is updated
$entityDefinitionLoader->forceSystemReset(ObjectTypes::USER);

// Update old system user GUIDs
$db->update('objects_user_act', ['guid'=>UserEntity::USER_CURRENT], ['uname' => 'current.user']);
$db->update('objects_user_act', ['guid'=>UserEntity::USER_ADMINISTRATOR], ['uname' => 'administrator']);
$db->update('objects_user_act', ['guid'=>UserEntity::USER_ANONYMOUS], ['uname' => 'anonymous']);
$db->update('objects_user_act', ['guid'=>UserEntity::USER_SYSTEM], ['uname' => 'system']);
$db->update('objects_user_act', ['guid'=>UserEntity::USER_WORKFLOW], ['uname' => 'workflow']);

// Update the uname of all users
$query = new EntityQuery(ObjectTypes::USER);
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
    $query = new EntityQuery(ObjectTypes::USER);
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
