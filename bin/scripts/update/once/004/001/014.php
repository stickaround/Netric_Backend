<?php
/**
 * Due to a bug we had a bunch of activities created without a ts_entered value set.
 * This script cleans them all up since they were really killing performance with all
 * the associations.
 */
use Netric\Db\DbFactory;
use Netric\Entity\EntityLoaderFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(DbFactory::class);
$loader = $serviceManager->get(EntityLoaderFactory::class);
$log = $account->getApplication()->getLog();

$iterations = 0;
$numDeleted = 0;

// Continue looping through pages to delete because we don't want to take the sever down with 1billion+ blank entities
while ($iterations < 1000 && $numDeleted > 1) {
    $iterations++;
    $sql = "SELECT id FROM objects_activity_act WHERE ts_entered IS NULL limit 10000";
    $results = $db->query($sql);
    $totalNum = $db->getNumRows($results);
    for ($i = 0; $i < $totalNum; $i++) {
        $row = $db->getRow($results, $i);
        $activity = $loader->get("activity", $row['id']);

        // Hard delete it if it exists
        if ($activity) {
            $loader->delete($activity, true);
            $numDeleted++;
            $log->info(
                "Update 004.001.013 deleted activity " .
                $activity->getId() .
                " which is " . ($i + 1) . "x" . $iterations
            );
        }

    }

    // Break the loop if no activities were found
    if ($totalNum === 0) {
        $numDeleted = 0;
    }
}
