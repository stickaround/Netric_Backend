<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module\DataMapper;

use Netric\Error\AbstractHasErrors;
use Netric\Account\Module\Module;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Config\Config;
use Netric\Entity\ObjType\UserEntity;
use SimpleXMLElement;


class ModuleRdbDataMapper extends AbstractHasErrors implements DataMapperInterface
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
     * Construct and initialize dependencies
     *
     * @param RelationalDbInterface $db
     * @param Config $config The configuration object
     */
    public function __construct(RelationalDbInterface $db, Config $config, UserEntity $user)
    {
        $this->db = $db;
        $this->config = $config;
        $this->user = $user;
    }

    /**
     * Save changes or create a new module
     *
     * @param Module $module The module to save
     * @return bool true on success, false on failure with details in $this->getLastError
     */
    public function save(Module $module)
    {
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
        );

        // Make sure that the module is dirty before we set the navigation
        if ($module->isDirty()) {
            $moduleNavigation = null;

            // Make sure the the navigation is an array
            if ($module->getNavigation() && is_array($module->getNavigation())) {
                // Setup the xml object
                $xmlNavigation = new SimpleXMLElement('<navigation></navigation>');

                // Now converte the module navigation data into xml
                $this->arrayToXml($module->getNavigation(), $xmlNavigation);

                // Save the xml string
                $moduleNavigation = $xmlNavigation->asXML();
            }

            // Set the module navigation
            $data["xml_navigation"] = $moduleNavigation;
        }

        // Compose either an update or insert statement
        $sql = "";
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

        // Settings navigation that will be displayed in the frontend
        $settingsData = array(
            "id" => null,
            "name" => "settings",
            "title" => "Settings",
            "short_title" => "Settings",
            "f_system" => "t"
        );

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
        $module->fromArray($row);

        /*
         * If module data from the database has xml_navigation, then we will use this to set the module's navigation
         * Otherwise, we will use the module navigation file
         */
        if (isset($row['xml_navigation']) && !empty($row['xml_navigation'])) {
            // Convert the xml navigation string into an array
            $xml = simplexml_load_string($row['xml_navigation']);
            $json = json_encode($xml);

            // Make sure that the navigation array is not an associative array
            $nav['navigation'] = array_values(json_decode($json, true));

            // Import the module data coming from the database
            $module->fromArray($nav);

            // Set the system value separately
            $module->setSystem(($row['f_system'] == 't') ? true : false);
        } else {
            // Get the location of the module navigation file
            $basePath = $this->config->get("application_path") . "/data";

            // Make sure that the pathy and file is existing
            if ($module->getName() && file_exists($basePath . "/modules/" . $module->getName() . ".php")) {
                $moduleData = include($basePath . "/modules/" . $module->getName() . ".php");

                // Import module data coming from the navigation fallback file
                $module->fromArray($moduleData);
            }

            // Flag this module as clean, since we just loaded navigation file
            $module->setDirty(false);
        }

        return $module;
    }

    /**
     * Convert the array data to xml
     *
     * @param array $data The module data that will be converted into xml string
     * @param SimpleXMLElement $xmlData The xml object that will be used to convert
     */
    private function arrayToXml(array $data, SimpleXMLElement &$xmlData)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                if (is_numeric($key)) {
                    $key = 'item' . $key; //dealing with <0/>..<n/> issues
                }
                $subnode = $xmlData->addChild($key);
                $this->arrayToXml($value, $subnode);
            } else {
                $xmlData->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }
}