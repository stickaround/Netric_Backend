<?php

/**
 * Add system-wide dashboards and widgets
 */
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoaderFactory;

$account = $this->getAccount();
if (!$account)
    throw new \RuntimeException("This must be run only against a single account");

$entityLoader = $account->getServiceManager()->get(EntityLoaderFactory::class);

$dashboardsData = require(__DIR__ . "/../../../../data/account/dashboard.php");
$dashboardWidgetData = require(__DIR__ . "/../../../../data/account/dashboard-widgets.php");

/*
 * Loop thru the system-wide dashboard and check if we need to create them
 */
foreach ($dashboardsData as $dashbordName => $dashboardData) {

    // Check first if we have already a system-wide dashboard
    $dashboardEntity = $entityLoader->getByUniqueName(ObjectTypes::DASHBOARD, $dashboardData['uname']);

    // If we do not have a system wide dashboard entity, then let's create it
    if (!$dashboardEntity) {
        $dashboardEntity = $entityLoader->create(ObjectTypes::DASHBOARD);
        $dashboardEntity->fromArray($dashboardData);
        $entityLoader->save($dashboardEntity);
    }

    // Get the widgets for the specific dashboard
    $widgetsData = $dashboardWidgetData[$dashbordName];
    foreach ($widgetsData as $widgetData) {

        // Let's check if the widget is already added in the system wide dashboard
        $widgetEntity = $entityLoader->getByUniqueName(ObjectTypes::DASHBOARD_WIDGET, $widgetData['uname'], array(
            "dashboard_id" => $dashboardEntity->getGuid(),
            "widget_name" => $widgetData["widget_name"]
        ));

        // If not, then let's create the widget for system wide dashboard
        if (!$widgetEntity) {
            $widgetEntity = $entityLoader->create(ObjectTypes::DASHBOARD_WIDGET);
            $widgetEntity->fromArray($widgetData);
            $widgetEntity->setValue("dashboard_id", $dashboardEntity->getGuid(), $dashboardEntity->getValue("name"));
            $entityLoader->save($widgetEntity);
        }
    }
}