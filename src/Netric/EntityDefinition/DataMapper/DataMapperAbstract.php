<?php
/**
 * A DataMapper is responsible for writing and reading data from a persistant store
 *
 * TODO: we are currently porting this over to v4 framework from v3
 * So far it has just been copied and the namespace replaced the prefix name
 *
 * @category    DataMapper
 * @author      Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright   Copyright (c) 2003-2013 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntityDefinition\DataMapper;

use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Config\ConfigFactory;

abstract class DataMapperAbstract extends \Netric\DataMapperAbstract
{
    /**
     * The type of object this data mapper is handling
     *
     * @var string
     */
    protected $objType = "";

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

        // Clear cache the if we have successfully deleted a definition
        if ($result) {
            $this->getLoader()->clearCache($def->getObjType());
        }

        return $result;
    }
 
    /**
     * Save a definition
     *
     * @param EntityDefinition $def The definition to save
     * @return string|bool entity id on success, false on failure
     */
    public function save(EntityDefinition $def)
    {
        // Increment revision
        $def->revision += 1;

        // Save data
        $this->saveDef($def);

        // Clear cache
        $this->getLoader()->clearCache($def->getObjType());
    }

    /**
     * Delete an object definition by name
     *
     * @var string $objType The name of the object type
     * @return bool true on success, false on failure
     */
    public function deleteByName($objType)
    {
        $def = $this->fetchByName($objType);
        return $this->delete($def);
    }
    
    /**
     * Get definition loader using this mapper
     *
     * @return EntityDefinitionLoader
     */
    public function getLoader()
    {
        return $this->getAccount()->getServiceManager()->get(EntityDefinitionLoaderFactory::class);
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
            throw new \InvalidArgumentException("Can do a system update on a custom entity definition");
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
        $config = $this->getAccount()->getServiceManager()->get(ConfigFactory::class);
        $basePath = $config->application_path . "/data/entity_definitions";
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
        $config = $this->getAccount()->getServiceManager()->get(ConfigFactory::class);
        $basePath = $config->application_path . "/data/entity_definitions";
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
