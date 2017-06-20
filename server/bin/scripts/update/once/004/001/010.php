<?php
/**
 * Move the old project_membership data to the project's member table (which is now an object multi)
 */

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get("Netric/Db/Db");
$loader = $serviceManager->get("Netric/EntityLoader");

$result = $db->query("SELECT * from project_membership");

for ($i = 0; $i < $db->getNumRows($result); $i++) {

    // Get the result row
    $row = $db->getRow($result, $i);

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
}