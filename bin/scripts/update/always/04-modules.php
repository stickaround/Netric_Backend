<?php

/**
 * Add default modules to each account
 */
use Netric\Account\Module\Module;

$account = $this->getAccount();
if (!$account)
    throw new \RuntimeException("This must be run only against a single account");

// Get modules from data
$modules = require(__DIR__ . "/../../../../data/account/modules.php");

// Get the module service
$serviceLocator = $account->getServiceManager();
$moduleService = $serviceLocator->get("Netric/Account/Module/ModuleService");

foreach ($modules as $moduleData) {
    $module = !$moduleService->getByName($moduleData['name']);
    if (!$module) {
        $module = new Module();
    }

    // Either save new or save changes
    $module->fromArray($moduleData);
    $moduleService->save($module);
}