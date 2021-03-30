<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Account\InitData\InitDataInterface;
use Netric\Account\Module\ModuleService;
use Netric\Account\Module\Module;

/**
 * Initializer to make sure accounts have a default set of groupings
 */
class ModulesInitData implements InitDataInterface
{
    /**
     * Array of modules to add
     */
    private array $modulesData = [];

    /**
     * Path to module data
     */
    private string $moduleDataDir;

    /**
     * Module service for interacting with modules in netric
     */
    private ModuleService $moduleService;

    /**
     * Constructor
     *
     * @param array $modulesData
     * @param ModuleService $moduleService
     */
    public function __construct(array $modulesData, string $moduleDataDir, ModuleService $moduleService)
    {
        $this->modulesData = $modulesData;
        $this->moduleDataDir = $moduleDataDir;
        $this->moduleService = $moduleService;
    }

    /**
     * Insert or update initial data for account
     *
     * @param Account $account
     * @return bool
     */
    public function setInitialData(Account $account): bool
    {
        foreach ($this->modulesData as $moduleName) {
            // Get the data definition for the module
            $moduleData = require($this->moduleDataDir . '/' . $moduleName . ".php");

            // If module is already saved then selectively update fields
            $module = $this->moduleService->getByName($moduleName, $account->getAccountId());
            if ($module) {
                // We do this in case the user has modified publish status or sort_order
                $module->setTitle($moduleData['title']);
                $module->setShortTitle($moduleData['short_title']);
                $module->setNavigation($moduleData['navigation']);
                $module->setSortOrder($moduleData['sort_order']);
            }

            // Module has not yet been added. Create a new module and import the data
            if (!$module) {
                $module = new Module();
                $module->fromArray($moduleData);
            }

            // Either save new or save changes
            $this->moduleService->save($module, $account->getAccountId());
        }

        return true;
    }
}
