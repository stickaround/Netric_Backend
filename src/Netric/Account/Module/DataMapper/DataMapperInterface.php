<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module\DataMapper;

use Netric\Error\ErrorAwareInterface;
use Netric\Account\Module\Module;

interface DataMapperInterface extends ErrorAwareInterface
{
    /**
     * Save changes or create a new module
     *
     * @param Module $module The module to save
     * @param string $accountId The account id that owns this module
     * @return bool true on success, false on failure with details in $this->getLastError
     */
    public function save(Module $module, string $accountId);

    /**
     * Get a module by name
     *
     * @param string $name The name of the module to retrieve
     * @param string $accountId The account id that owns the module
     * @return Module|null
     */
    public function get(string $name, string $accountId);

    /**
     * Get all modules installed in this account
     *
     * @param string $accountId The account id that owns the modules
     * @param string $scope One of the defined scopes in Module::SCOPE_*
     * @return Module[]
     */
    public function getAll(string $accountId, string $scope = "");

    /**
     * Delete a non-system module
     *
     * @param Module $module Module to delete
     * @return bool true on success, false on failure with details in $this->getLastError
     */
    public function delete(Module $module);
}
