<?php

/**
 * Move the old project_membership data to the project's member table (which is now an object multi)
 */

use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\EntityLoaderFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);
$loader = $serviceManager->get(EntityLoaderFactory::class);
$log = $account->getApplication()->getLog();

if (!$db->tableExists('project_membership')) {
    return true;
}

$projectMemberships = [];
$result = $db->query("SELECT * from project_membership");

foreach ($result->fetchAll() as $row) {
    $projectId = $row['project_id'];
    $projectMemberships[$projectId][] = $row;
}

foreach ($projectMemberships as $projectId => $members) {
    $projectEntity = $loader->get("project", $projectId);
    $projectEntity->clearMultiValues("members");

    foreach ($members as $member) {
        $projectEntity->addMultiValue("members", $member['user_id']);
    }

    $loader->save($projectEntity);

    $log->info("Update 004.001.010 changed project_membership to users for $projectId:{$member['user_id']}");
}
