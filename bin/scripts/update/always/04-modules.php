<?php

/**
 * Add default modules to each account
 */
use Netric\Account\Module\Module;
use Netric\Account\Module\ModuleServiceFactory;

$account = $this->getAccount();
if (!$account)
    throw new \RuntimeException("This must be run only against a single account");

// Get modules from data
$modules = require(__DIR__ . "/../../../../data/account/modules.php");

// Get the module service
$serviceLocator = $account->getServiceManager();
$moduleService = $serviceLocator->get(ModuleServiceFactory::class);

foreach ($modules as $moduleData) {
    $module = $moduleService->getByName($moduleData['name']);

    // If module is already saved then selectively update fields
    if ($module) {
        // We do this in case the user has modified publish status or sort_order
        $module->setTitle($moduleData['title']);
        $module->setShortTitle($moduleData['short_title']);
    }

    // Module has not yet been added. Create a new module and import the data
    if (!$module) {
        $module = new Module();
        $module->fromArray($moduleData);
    }

    // Either save new or save changes
    $moduleService->save($module);
}