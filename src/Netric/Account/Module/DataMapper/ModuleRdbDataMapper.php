<?php

namespace Netric\Account\Module\DataMapper;

use Netric\Error\AbstractHasErrors;
use Netric\Account\Module\Module;
use Netric\Account\Module\DataMapper\DataMapperInterface as ModuleDataMapperInterface;
use Netric\Db\Relational\RelationalDbInterface;
use Aereus\Config\Config;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityLoader;

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
     * Entity loader will be used to load the module
     */
    private EntityLoader $entityLoader;

    /**
     * Table where we store module data
     */
    const TABLE_MODULES = 'account_module';

    /**
     * Construct and initialize dependencies
     *
     * @param RelationalDbInterface $db
     * @param Config $config The configuration object
     */
    public function __construct(RelationalDbInterface $db, Config $config, EntityLoader $entityLoader)
    {
        $this->db = $db;
        $this->config = $config;
        $this->entityLoader = $entityLoader;
    }

    /**
     * Save changes or create a new module
     *
     * @param Module $module The module to save
     * @param string $accountId The account id that owns this module
     * @return bool true on success, false on failure with details in $this->getLastError
     */
    public function save(Module $module, string $accountId)
    {
        // Setup data for the database columns
        $data = [
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
            "navigation_data" => json_encode($module->getNavigation()),
            "xml_navigation" => $module->getXmlNavigation(),
            'account_id' => $accountId,
        ];

        // Compose either an update or insert statement
        if ($module->getModuleId()) {
            $this->db->update(self::TABLE_MODULES, $data, [self::TABLE_MODULES . '_id' => $module->getModuleId()]);
            return true;
        }

        $id = $this->db->insert(self::TABLE_MODULES, $data, self::TABLE_MODULES . '_id');
        $module->setModuleId($id);
        return true;
    }

    /**
     * Get a module by name
     *
     * @param string $name The name of the module to retrieve
     * @param string $accountId The account id that owns the module
     * @return Module|null
     */
    public function get(string $name, string $accountId)
    {
        $sql = 'SELECT * FROM ' . self::TABLE_MODULES . ' WHERE name=:name AND account_id=:account_id';
        $result = $this->db->query($sql, ['name' => $name, 'account_id' => $accountId]);

        if ($result->rowCount()) {
            $row = $result->fetch();
            return $this->createModuleFromRow($row, $accountId);
        }

        // Not found
        return null;
    }

    /**
     * Get all modules installed in this account
     *
     * @param string $accountId The account id that owns the modules
     * @param string $scope One of the defined scopes in Module::SCOPE_*
     * @return Module[]|null on error
     */
    public function getAll(string $accountId, string $scope = "")
    {
        $modules = [];

        $sql = 'SELECT * FROM ' . self::TABLE_MODULES;
        $sql .= ' WHERE account_id=:account_id';
        $whereCond = [
            'account_id' => $accountId
        ];
        if ($scope) {
            $sql .= ' AND scope=:scope';
            $whereCond['scope'] = $scope;
        }

        $sql .= ' ORDER BY sort_order';
        $result = $this->db->query($sql, $whereCond);

        foreach ($result->fetchAll() as $row) {
            $modules[] = $this->createModuleFromRow($row, $accountId);
        }

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

        if (!$module->getModuleId()) {
            throw new \InvalidArgumentException("Missing ID - cannot delete an unsaved module");
        }

        $this->db->delete(self::TABLE_MODULES, [self::TABLE_MODULES . '_id' => $module->getModuleId()]);
        return true;
    }

    /**
     * Translate row data to module properties and return instance
     *
     * @param array $row The associative array of column data from a row
     * @param string $accountId The account id that owns the module
     * @return Module
     */
    private function createModuleFromRow(array $row, string $accountId)
    {
        $module = new Module();

        // Make sure that we set an 'id' in the row from the table id
        if (!isset($row['id'])) {
            $row['id'] = $row[self::TABLE_MODULES . '_id'];
        }
        // Now, Import the module data coming from the database and override what was set using the default navigation file
        $module->fromArray($row);

        // Convert navigation data to an array and set
        if (isset($row['navigation_data']) && !empty($row['navigation_data'])) {
            $module->setNavigation(json_decode($row['navigation_data'], true));
        }

        // Set the system value separately
        $module->setSystem(($row['f_system'] == 't') ? true : false);

        // Update the foreign values of the module (user_id and team_id)
        $this->setUserAndTeamNamesFromIds($module, $accountId);

        // Flag this module as clean since we just loaded it
        $module->setDirty(false);

        return $module;
    }

    /**
     * Update the forieng values of the module
     *
     * @param Module $module The module that we will be updating the foreign values
     * @param string $accountId The account id that owns the module
     */
    public function setUserAndTeamNamesFromIds(Module $module, string $accountId)
    {
        // Make sure we reset the all foreign values first before setting new values
        $module->setUserName(null);
        $module->setTeamName(null);

        // Set the user name in the module if the user_id is set
        if ($module->getUserId()) {
            $userEntity = $this->entityLoader->getEntityById($module->getUserId(), $accountId);
            $module->setUserName($userEntity->getName());
        }

        // Set the team name in the module if the team_id is set
        if ($module->getTeamId()) {
            $teamEntity = $this->entityLoader->getEntityById($module->getTeamId(), $accountId);
            $module->setTeamName($teamEntity->getName());
        }
    }
}
