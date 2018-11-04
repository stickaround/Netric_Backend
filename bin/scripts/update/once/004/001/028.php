<?php
/**
 * Add the legacy dashboard widgets to app_widgets table if they did not exist
 */
use Netric\Db\Relational\RelationalDbFactory;

$account = $this->getAccount();
$serviceManager = $account->getServiceManager();
$db = $serviceManager->get(RelationalDbFactory::class);

// Make sure system users have the right uname and guid
$legacyWidgets = require(__DIR__ . "/../../../../../../data/account/legacy-app-widgets.php");
foreach ($legacyWidgets as $widgetData) {
    $result = $db->query(
        "SELECT * FROM app_widgets WHERE class_name=:class_name",
        ["class_name" => $widgetData['class_name']]
    );

    print_r($widgetData);
    echo $result->rowCount();
    // If the widget does not exist then we will add it in the app_wdgiets table
    if ($result->rowCount() === 0) {
        $db->insert("app_widgets", $widgetData);
    }
}
