<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module\DataMapper;

use Netric\Error\AbstractHasErrors;
use Netric\Account\Module\Module;
use Netric\Db\DbInterface;

class DataMapperDb extends AbstractHasErrors implements DataMapperInterface
{
    /**
     * Handle to account database
     *
     * @var DbInterface
     */
    private $dbh = null;

    /**
     * Construct and initialize dependencies
     *
     * @param DbInterface $dbh
     */
    public function __construct(DbInterface $dbh)
    {
        $this->dbh = $dbh;
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
            "id" => $this->dbh->escapeNumber($module->getId()),
            "name" => "'" . $this->dbh->escape($module->getName()) . "'",
            "title" => "'" . $this->dbh->escape($module->getTitle()) . "'",
            "short_title" => "'" . $this->dbh->escape($module->getShortTitle()) . "'",
            "scope" => "'" . $this->dbh->escape($module->getScope()) . "'",
            "f_system" => ($module->isSystem()) ? "'t'" : "'f'",
            "user_id" => $this->dbh->escapeNumber($module->getUserId()),
            "team_id" => $this->dbh->escapeNumber($module->getTeamId()),
            "sort_order" => $this->dbh->escapeNumber($module->getSortOrder()),
        );

        // Compose either an update or insert statement
        $sql = "";
        if ($module->getId()) {
            // Update existing record
            $updateStatements = "";
            foreach ($data as $colName=>$colValue) {
                if ($updateStatements) $updateStatements .= ", ";
                $updateStatements .= $colName . "=" . $colValue;
            }
            $sql = "UPDATE applications SET $updateStatements " .
                   "WHERE id=" . $this->dbh->escapeNumber($module->getId()) . ";" .
                   "SELECT " . $this->dbh->escapeNumber($module->getId()) ." as id;";
        } else {
            // Insert new record
            $columns = [];
            $values = [];
            foreach ($data as $colName=>$colValue) {
                if ($colName != 'id') {
                    $columns[] = $colName;
                    $values[] = $colValue;
                }
            }

            $sql = "INSERT INTO applications(" . implode(',', $columns) . ") " .
                   "VALUES(" . implode(',', $values) . ") RETURNING id";
        }

        // Run the query and return the results
        $result = $this->dbh->query($sql);
        if (!$result)
        {
            $this->addErrorFromMessage($this->dbh->getLastError());
            return false;
        }

        // Update the module id
        if ($this->dbh->getNumRows($result) && !$module->getId())
        {
            $module->setId($this->dbh->getValue($result, 0, 'id'));
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
        $sql = "SELECT * FROM applications WHERE name='" . $this->dbh->escape($name) . "'";
        $result = $this->dbh->query($sql);
        if (!$result)
        {
            $this->addErrorFromMessage($this->dbh->getLastError());
            return null;
        }

        if ($this->dbh->getNumRows($result)) {
            $row = $this->dbh->getRow($result, 0);
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

        $sql = "SELECT * FROM applications ";
        if ($scope)
            $sql .= "WHERE scope='" . $this->dbh->escape($scope) . "' ";
        $sql .= "ORDER BY sort_order";
        $result = $this->dbh->query($sql);
        if (!$result)
        {
            $this->addErrorFromMessage($this->dbh->getLastError());
            return null;
        }

        $num = $this->dbh->getNumRows($result);
        for ($i = 0; $i < $num; $i++) {
            $row = $this->dbh->getRow($result, $i);
            $modules[] = $this->createModuleFromRow($row);
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
        if ($module->isSystem())
        {
            $this->addErrorFromMessage("Cannot delete a system module");
            return false;
        }

        if (!$module->getId())
        {
            throw new \InvalidArgumentException("Missing ID - cannot delete an unsaved module");
        }

        $sql = "DELETE FROM applications WHERE id=" . $this->dbh->escapeNumber($module->getId());
        $result = $this->dbh->query($sql);

        // Check to see if there was a problem
        if (!$result)
        {
            $this->addErrorFromMessage("DB error: " . $this->dbh->getLastError());
            return false;
        }

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
        $module->setId($row['id']);
        $module->setName($row['name']);
        $module->setTitle($row['title']);
        $module->setShortTitle($row['short_title']);
        $module->setSystem(($row['f_system'] == 't') ? true : false);

        // Now add columns that may not be set
        if ($row['scope'])
            $module->setScope($row['scope']);

        if ($row['user_id'])
            $module->setUserId($row['user_id']);

        if ($row['team_id'])
            $module->setTeamId($row['team_id']);

        if ($row['sort_order'])
            $module->setSortOrder($row['sort_order']);

        return $module;
    }
}