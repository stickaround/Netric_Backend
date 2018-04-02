<?php

/**
 * Add system wide dashboard and its widgets
 */

$account = $this->getAccount();
if (!$account)
    throw new \RuntimeException("This must be run only against a single account");

/*
 * First create the system wide dashboard
 */
$dashboardData = require(__DIR__ . "/../../../../data/account/dashboard.php");
$entityLoader = $account->getServiceManager()->get("EntityLoader");

// Check first if we have already a system-wide dashboard
$dashboardEntity = $entityLoader->getByUniqueName("dashboard", $dashboardData['uname']);

// If we do not have a system wide dashboard entity, then let's create it
if (!$dashboardEntity) {
    $dashboardEntity = $entityLoader->create("dashboard");
    $dashboardEntity->fromArray($dashboardData);
    $entityLoader->save($dashboardEntity);
}

// Now add the widgets for system wide dashboard
$dashboardWidgetData = require(__DIR__ . "/../../../../data/account/dashboard-widgets.php");

foreach ($dashboardWidgetData as $widgetData) {

    // Let's check if the widget is already added in the system wide dashboard
    $widgetEntity = $entityLoader->getByUniqueName("dashboard_widget", $widgetData['uname'], array(
        "dashboard_id" => $dashboardEntity->getValue("id"),
        "widget_name" => $widgetData["widget_name"]
    ));

    // If not, then let's create the widget for system wide dashboard
    if (!$widgetEntity) {
        $widgetEntity = $entityLoader->create("dashboard_widget");
        $widgetEntity->fromArray($widgetData);
        $widgetEntity->setValue("dashboard_id", $dashboardEntity->getValue("id"), $dashboardEntity->getValue("name"));
        $entityLoader->save($widgetEntity);
    }
}