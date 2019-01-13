<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module\DataMapper;

use Netric\Error\AbstractHasErrors;
use Netric\Account\Module\Module;
use Netric\Account\Module\DataMapper\DataMapperInterface as ModuleDataMapperInterface;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Config\Config;
use Netric\Entity\ObjType\UserEntity;
use Netric\Account\Account;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

class ModuleRdbDataMapper extends AbstractHasErrors implements ModuleDataMapperInterface
{
    /**
     * Handle to account database
     *
     * @var RelationalDbInterface
     */
    private $db = null;

    /**
     * Netric configuration
     *
     * @var Config
     */
    private $config = null;

    /**
     * Current user
     *
     * @var UserEntity
     */
    private $user = null;

    /**
     * Current account
     *
     * @var Account
     */
    private $account = null;

    /**
     * Construct and initialize dependencies
     *
     * @param RelationalDbInterface $db
     * @param Config $config The configuration object
     */
    public function __construct(RelationalDbInterface $db, Config $config, Account $account)
    {
        $this->db = $db;
        $this->config = $config;
        $this->account = $account;
        $this->user = $account->getUser();
    }

    /**
     * Save changes or create a new module
     *
     * @param Module $module The module to save
     * @return bool true on success, false on failure with details in $this->getLastError
     */
    public function save(Module $module)
    {
        $navigationData = $module->getNavigation() ? $module->getNavigation() : [];
        // Setup data for the database columns
        $data = array(
            "name" => $module->getName(),
            "title" => $module->getTitle(),
            "short_title" => $module->getShortTitle(),
            "scope" => $module->getScope(),
            "f_system" => $module->isSystem(),
            "user_id" => $module->getUserId(),
            "team_id" => $module->getTeamId(),
            "sort_order" => $module->getSortOrder(),
            "icon" => $module->getIcon(),
            "default_route" => $module->getDefaultRoute(),
            "navigation_data" => json_encode($navigationData),
            "xml_navigation" => $module->convertNavigationToXml()
        );

        // Compose either an update or insert statement
        if ($module->getId()) {
            $this->db->update('applications', $data, ['id' => $module->getId()]);
        } else {
            $id = $this->db->insert('applications', $data);
            $module->setId($id);
        }

        return true;
    }

    /**
     * Get a module by name
     *
     * @param string $name The name of the module to retrieve
     * @return Module|null
     */
    public function get($name)
    {
        $sql = 'SELECT * FROM applications WHERE name=:name';
        $result = $this->db->query($sql, ['name' => $name]);

        if ($result->rowCount()) {
            $row = $result->fetch();
            return $this->createModuleFromRow($row);
        }

        // Not found
        return null;
    }

    /**
     * Get all modules installed in this account
     *
     * @param string $scope One of the defined scopes in Module::SCOPE_*
     * @return Module[]|null on error
     */
    public function getAll($scope = null)
    {
        $modules = [];

        $sql = 'SELECT * FROM applications ';
        $whereCond = [];
        if ($scope) {
            $sql .= 'WHERE scope=:scope ';
            $whereCond['scope'] = $scope;
        }

        $sql .= 'ORDER BY sort_order';
        $result = $this->db->query($sql, $whereCond);

        foreach ($result->fetchAll() as $row) {
            $modules[] = $this->createModuleFromRow($row);
        }

        $modules['settings'] = $this->createModuleFromRow($settingsData);

        return $modules;
    }

    /**
     * Delete a non-system module
     *
     * @param Module $module Module to delete
     * @return bool true on success, false on failure with details in $this->getLastError
     */
    public function delete(Module $module)
    {
        if ($module->isSystem()) {
            $this->addErrorFromMessage("Cannot delete a system module");
            return false;
        }

        if (!$module->getId()) {
            throw new \InvalidArgumentException("Missing ID - cannot delete an unsaved module");
        }

        $this->db->delete('applications', ['id' => $module->getId()]);
        return true;
    }

    /**
     * Translate row data to module properties and return instance
     *
     * @param array $row The associative array of column data from a row
     * @return Module
     */
    private function createModuleFromRow(array $row)
    {
        $module = new Module();

        /*
         * Legacy settings used to be stored in xml_navigation
         * but we no longer use this
         *
        if (isset($row['xml_navigation']) && !empty($row['xml_navigation'])) {
            // Convert the xmlNavigation to array
            $navigation = $module->convertXmltoNavigation($row['xml_navigation']);
            $module->setNavigation($navigation);
        }
        */

        // Now, Import the module data coming from the database and override what was set using the default navigation file
        $module->fromArray($row);

        // Convert navigation data to an array and set
        if (isset($row['navigation_data']) && !empty($row['navigation_data'])) {
            $module->setNavigation(json_decode($row['navigation_data'], true));
        }

        // Set the system value separately
        $module->setSystem(($row['f_system'] == 't') ? true : false);

        // Update the foreign values of the module (user_id and team_id)
        $this->setUserAndTeamNamesFromIds($module);

        // Flag this module as clean since we just loaded it
        $module->setDirty(false);

        return $module;
    }

    /**
     * Update the forieng values of the module
     *
     * @param Module $module The module that we will be updating the foreign values
     */
    public function setUserAndTeamNamesFromIds(Module &$module)
    {
        // Make sure we reset the all foreign values first before setting new values
        $module->setUserName(null);
        $module->setTeamName(null);

        // Set the user name in the module if the user_id is set
        if ($module->getUserId()) {
            $userEntity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->get(ObjectTypes::USER, $module->getUserId());
            $module->setUserName($userEntity->getName());
        }

        // Set the team name in the module if the team_id is set
        if ($module->getTeamId()) {
            $teamEntity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->get(ObjectTypes::USER_TEAM, $module->getTeamId());
            $module->setTeamName($teamEntity->getName());
        }
    }
}
