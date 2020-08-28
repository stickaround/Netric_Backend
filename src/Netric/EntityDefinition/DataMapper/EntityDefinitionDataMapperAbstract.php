<?php

namespace Netric\EntityDefinition\DataMapper;

use Netric\DataMapperAbstract;
use Netric\EntityDefinition\EntityDefinition;
use Netric\WorkerMan\Worker\EntityDefinitionPostSaveWorker;
use Netric\WorkerMan\WorkerService;
use Aereus\Config\Config;

/**
 * A DataMapper is responsible for writing and reading data from a persistant store
 */
abstract class EntityDefinitionDataMapperAbstract extends DataMapperAbstract
{
    /**
     * Used to schedule background jobs
     * 
     * @var WorkerService
     */
    private WorkerService $workerService;

    /**
     * Config loader that will be used to load the system config
     *
     * @var Config
     */
    private $config = null;

    /**
     * Class constructor
     *
     * @param AccountServiceManager $serviceManager
     */
    public function __construct(        
        WorkerService $workerService,
        Config $config
    ) {
        $this->workerService = $workerService;
        $this->config = $config;        
    }

    /**
     * Delete object definition
     *
     * @param EntityDefinition $def The definition to delete
     * @return bool true on success, false on failure
     */
    abstract public function deleteDef(EntityDefinition $def);

    /**
     * Save a definition
     *
     * @param EntityDefinition $def The definition to save
     * @return string|bool entity id on success, false on failure
     */
    abstract public function saveDef(EntityDefinition $def);

    /**
     * Delete object definition
     *
     * @param EntityDefinition $def The definition to delete
     * @return bool true on success, false on failure
     */
    public function delete(EntityDefinition $def)
    {
        $result = $this->deleteDef($def);

        // Check for result if we have successfully deleted the entity definition
        if ($result) {
            $this->postSaveWorker($def);
        }

        return $result;
    }

    /**
     * 
     * Save a definition
     *
     * @param EntityDefinition $def The definition to save
     * @return string|bool entity id on success, false on failure
     */
    public function save(EntityDefinition $def)
    {
        // Increment revision
        $def->revision += 1;

        // Save the entity definition data
        $result = $this->saveDef($def);

        // Check for result if we have successfully saved the entity definition
        if ($result) {
            $this->postSaveWorker($def);
        }

        return $result;
    }

    /**
     * Send background job to do less expedient (but no less important) tasks
     * 
     * @param EntityDefinition $def The definition we are currently working on
     */
    private function postSaveWorker(EntityDefinition $def) {
        $this->workerService->doWorkBackground(EntityDefinitionPostSaveWorker::class, [
            'entity_definition_id' => $def->getEntityDefinitionId(),
            'account_id' => $def->getAccountId(),
            'obj_type' => $def->getObjType(),
        ]);
    }

    /**
     * Delete an object definition by name
     *
     * @param string $objType The name of the object type
     * @param string $accountId The account that owns the entity definition
     * 
     * @return bool true on success, false on failure
     */
    public function deleteByName(string $objType, string $accountId)
    {
        $def = $this->fetchByName($objType, $accountId);
        return $this->delete($def);
    }

    /**
     * Update a definition from the local system in data/entity_definitions
     *
     * @param EntityDefinition $def
     * @throws \InvalidArgumentException If a non-system definition is passed in
     * @return bool true if definition was updated, false if no updates were made
     */
    public function updateSystemDefinition(EntityDefinition $def)
    {
        if (!$def->system) {
            throw new \InvalidArgumentException("Can't do a system update on a custom entity definition");
        }

        // Check if this is a system object and if it is
        $sysData = $this->getSysDef($def->getObjType());
        if ($sysData) {
            // Check to see if the sysData has changed since the last time we loaded it
            $systemDefinitionHash = $this->getLatestSystemDefinitionHash($def->getObjType());
            if ($systemDefinitionHash != $def->systemDefinitionHash) {
                // System definition has been updated, save the changes
                $def->fromArray($sysData);

                // Update hash to keep from applying this same change in the future
                $def->systemDefinitionHash = $systemDefinitionHash;
                $this->save($def);
                return true;
            }
        }

        return false;
    }

    /**
     * Get the latest hash for a system definition from the file system
     *
     * This is often used for cache breaking in loaders
     *
     * @param string $objType
     * @return string
     */
    public function getLatestSystemDefinitionHash(string $objType)
    {
        $sysData = $this->getSysDef($objType);
        return ($sysData) ? md5(json_encode($sysData)) : "";
    }

    /**
     * Load the definition from the filesystem to see if it has been updated since our last asve
     *
     * @param string $objType The name of the object to pull
     * @return array|null Array if found, null if not a system object with a definition
     */
    private function getSysDef($objType)
    {
        $ret = null;

        // Check for system object        
        $basePath = $this->config->application_path . "/data/entity_definitions";
        if (file_exists($basePath . "/" . $objType . ".php")) {
            $ret = include($basePath . "/" . $objType . ".php");

            if (is_array($ret['fields'])) {
                foreach ($ret['fields'] as $fname => $fld) {
                    $ret['fields'][$fname]["system"] = true;
                }
            }
        }

        return $ret;
    }

    /**
     * Get system aggregates
     *
     * @param EntityDefinition $def The definiition to load aggregates into
     */
    private function setSysAggregates(EntityDefinition $def)
    {
        // Check for system object
        $basePath = $this->config->application_path . "/data/entity_definitions";
        if (file_exists($basePath . "/" . $def->getObjType() . ".php")) {
            $ret = include($basePath . "/" . $def->getObjType() . ".php");

            if (is_array($ret['aggregates'])) {
                foreach ($ret['aggregates'] as $name => $aggData) {
                    $agg = new \stdClass();
                    $agg->field = $aggData['ref_obj_update'];
                    $agg->refField = $aggData['obj_field_to_update'];
                    $agg->calcField = $aggData['calc_field'];
                    $agg->type = $aggData['type'];
                    $def->addAggregate($agg);
                }
            }
        }
    }
}
