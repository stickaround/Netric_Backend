<?php
/**
 * Due to a bug we had a bunch of activities created without a ts_entered value set.
 * This script cleans them all up since they were really killing performance with all
 * the associations.
 */
$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get("Netric/Db/Db");
$loader = $serviceManager->get("Netric/EntityLoader");
$log = $account->getApplication()->getLog();

$iterations = 0;
$deleted = 0;

// Continue looping through pages to delete because we don't want to take the sever down with 93 million+ blank entities
while ($iterations < 1000 && $deleted > 1) {
    $iterations++;
    $sql = "select id from objects_activity_act where ts_entered is not null limit 10000";
    $results = $db->query($sql);
    $totalNum = $db->getNumRows($results);
    for ($i = 0; $i < $totalNum; $i++) {
        $row = $db->getRow($results, $i);
        $activity = $loader->get("activity", $row['id']);

        // Hard delete it if it exists
        if ($activity) {
            $loader->delete($activity, true);
            $deleted++;
            $log->info(
                "Update 004.001.013 deleted activity " .
                $activity->getId() .
                " which is " . ($i + 1) . "x" . $iterations
            );
        }

    }

    // Break the loop if no activities were found
    if ($totalNum === 0) {
        $deleted = 0;
    }
}
