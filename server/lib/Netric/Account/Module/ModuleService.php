<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module;

use Netric\Entity\ObjType\UserEntity;
use Netric\Config\Config;

/**
 * Service for working with modules
 */
class ModuleService
{
    /**
     * DataMapper for CRUD operations on modules
     *
     * @var DataMapper\DataMapperInterface
     */
    private $moduleDataMapper = null;

    /**
     * Netric configuration
     *
     * @var \Netric\Config
     */
    private $config = null;

    /**
     * Construct and set dependencies
     *
     * @param DataMapper\DataMapperInterface $dm
     * @param Config $config The configuration object
     */
    public function __construct(DataMapper\DataMapperInterface $dm, Config $config)
    {
        $this->moduleDataMapper = $dm;
        $this->config = $config;
    }

    /**
     * Retrieve a module by name
     *
     * @param string $name Unique name of module to load
     * @return Module|null if not found
     */
    public function getByName($name)
    {
        return $this->moduleDataMapper->get($name);
    }

    /**
     * Get a module by id
     *
     * @param int $id Unique id of module to get
     * @return Module|null if not found
     */
    public function getById($id)
    {
        $all = $this->moduleDataMapper->getAll();
        foreach ($all as $module) {
            if ($module->getId() == $id) {
                return $module;
            }
        }

        return null;
    }

    /**
     * Load modules for a specific user
     *
     * @param UserEntity $user The user to get modules for
     * @return Module[]
     */
    public function getForUser(UserEntity $user)
    {
        $all = $this->moduleDataMapper->getAll();
        $userModules = [];

        // Loop through each module to see if it applies to the user
        foreach ($all as $module) {
            if (
                $module->getScope() == Module::SCOPE_EVERYONE
                || (
                    $module->getScope() == Module::SCOPE_USER &&
                    $module->getUserId() == $user->getId()
                )
                || (
                    $module->getScope() == Module::SCOPE_USER &&
                    $module->getTeamId() == $user->getValue("team_id")
                )
            ) {
                $userModules[] = $module;
            }
        }

        return $userModules;
    }

    /**
     * Save changes to a module
     *
     * @param Module $module
     * @return bool
     */
    public function save(Module $module)
    {
        return $this->moduleDataMapper->save($module);
    }

    /**
     * Delete a module
     *
     * @param Module $module
     * @return bool
     */
    public function delete(Module $module)
    {
        return $this->moduleDataMapper->delete($module);
    }

    /**
     * Function that will get the additional info of the module (e.g. default_route, naviagtion links, and nav icon)
     *
     * @param Module $module
     * @return Module
     */
    public function getAdditionalModuleInfo(Module $module)
    {
        // Check for system object
        $basePath = $this->config->get("application_path") . "/objects";
        if ($module->getName() && file_exists($basePath . "/modules/" . $module->getName() . ".php")) {
            $moduleData = include($basePath . "/modules/" . $module->getName() . ".php");

            // Import the additional data of the module
            $module->fromArray($moduleData);

        }

        return $module;
    }

    /**
     * Function that will create the settings module
     *
     * @return Module
     */
    public function getSettingsModule()
    {
        // Create the new instance module for settings
        $module = new Module();

        // Since this is a custom settings module, we will set the id to -1
        $module->setId(-1);
        $module->setName("settings");

        // Get the additional info and return the module
        $settingsModule = $this->getAdditionalModuleInfo($module);

        return $settingsModule;
    }
}
