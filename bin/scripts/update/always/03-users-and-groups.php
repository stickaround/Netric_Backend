<?php

use Netric\EntityGroupings\Group;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\FileSystem\FileSystemFactory;

$account = $this->getAccount();
if (!$account) {
    throw new \RuntimeException("This must be run only against a single account");
}

/*
 * First make sure default user groups exist
 */
$groupsData = require(__DIR__ . "/../../../../data/account/user-groups.php");
$groupingsLoader = $account->getServiceManager()->get(GroupingLoaderFactory::class);
$groupings = $groupingsLoader->get(ObjectTypes::USER . "/groups", $account->getAccountId());
foreach ($groupsData as $groupData) {
    if (!$groupings->getByName($groupData['name'])) {
        $group = new Group();
        $group->name = $groupData['name'];
        $group->setDirty(); // Force update
        $groupings->add($group);
    }
}
$groupingsLoader->save($groupings);

// Create the root folder for this account
$fileSystem = $account->getServiceManager()->get(FileSystemFactory::class);
$fileSystem->setRootFolder($account->getSystemUser());

/*
 * Now make sure default users exists - with no password so no login
 */
$usersData = require(__DIR__ . "/../../../../data/account/users.php");
$entityLoader = $account->getServiceManager()->get(EntityLoaderFactory::class);

foreach ($usersData as $userData) {
    if (!$entityLoader->getByUniqueName(ObjectTypes::USER, $userData['name'], $account->getAccountId())) {
        $user = $entityLoader->create(ObjectTypes::USER, $account->getAccountId());
        $user->fromArray($userData);
        $entityLoader->save($user, $account->getSystemUser());
    }
}
