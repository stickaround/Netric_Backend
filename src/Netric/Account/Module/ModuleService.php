<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module;

use Netric\Entity\ObjType\UserEntity;

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
     * Construct and set dependencies
     *
     * @param DataMapper\DataMapperInterface $dm
     */
    public function __construct(DataMapper\DataMapperInterface $dm)
    {
        $this->moduleDataMapper = $dm;
    }

    /**
     * Retrieve a module by name
     *
     * @param string $name Unique name of module to load
     * @param string $accountId The account id that owns the module
     * @return Module|null if not found
     */
    public function getByName(string $name, string $accountId)
    {
        return $this->moduleDataMapper->get($name, $accountId);
    }

    /**
     * Get a module by id
     *
     * @param int $id Unique id of module to get
     * @param string $accountId The account id that owns the modules
     * @return Module|null if not found
     */
    public function getById($id, string $accountId)
    {
        $all = $this->moduleDataMapper->getAll($accountId);
        foreach ($all as $module) {
            if ($module->getModuleId() == $id) {
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
        $all = $this->moduleDataMapper->getAll($user->getAccountId());
        $userModules = [];

        // Loop through each module to see if it applies to the user
        foreach ($all as $module) {
            if ($module->getScope() == Module::SCOPE_EVERYONE
                || (
                    $module->getScope() == Module::SCOPE_USER &&
                    $module->getUserId() == $user->getEntityId()
                )
                || (
                    $module->getScope() == Module::SCOPE_TEAM &&
                    $module->getTeamId() == $user->getValue("team_id")
                )
            ) {
                $userModules[] = $module;
            }
        }

        return $userModules;
    }

    /**
     * Creates a new module
     *
     * @return bool
     */
    public function createNewModule()
    {
        return new Module();
    }

    /**
     * Save changes to a module
     *
     * @param Module $module The module to save
     * @param string $accountId The account id that owns this module
     * @return bool
     */
    public function save(Module $module, string $accountId)
    {
        return $this->moduleDataMapper->save($module, $accountId);
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
}
