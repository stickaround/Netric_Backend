<?php

namespace Netric\EntityDefinition;

use Aereus\Config\Config;
use Netric\Account\Account;
use Netric\Cache\CacheInterface;
use Netric\Entity\BrowserView\BrowserView;
use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperInterface;

/**
 * Class to handle to loading of object definitions
 */
class EntityDefinitionLoader
{
    /**
     * The current data mapper we are using for this object
     *
     * @var EntityDefinitionDataMapperInterface
     */
    protected $dataMapper = null;

    /**
     * Array of loaded entities
     *
     * @var array
     */
    private $loadedDefinitions = [];

    /**
     * Cache
     *
     * @var CacheInterface
     */
    private $cache = null;

    /**
     * Config loader that will be used to load the system config
     *
     * @var Config
     */
    private $config = null;

    /**
     * Setup IdentityMapper for loading objects
     *
     * @param EntityDefinitionDataMapperInterface $definitionDataMapper Datamapper for entity definitions
     * @param Config $config Config loader that will be used to load the system config
     * @param CacheInterface $cache Optional cache object
     * 
     * @return EntityDefinitionLoader
     */
    public function __construct(
        EntityDefinitionDataMapperInterface $definitionDataMapper,
        Config $config,
        CacheInterface $cache = null
    ) {
        $this->dataMapper = $definitionDataMapper;
        $this->config = $config;
        $this->cache = $cache;
    }

    /**
     * Get an entity
     *
     * @param string $objType The object type of the entity definition we are getting
     * @param string $accountId The account that owns the entity definition
     * 
     * @return EntityDefinition
     * @throws \InvalidArgumentException if non-string was passed
     */
    public function get(string $objType, string $accountId)
    {
        if (!$objType || !is_string($objType)) {
            throw new \InvalidArgumentException('ObjType Paramater is required');
        }

        if ($this->isLoaded($objType)) {
            return $this->loadedDefinitions[$objType];
        }

        return $this->loadDefinition($objType, $accountId);
    }

    /**
     * Retrieve an entity definition by id
     *
     * @param string $entityDefinitionId The id of the entity definition we are going to get
     * @param string $accountId The account that owns the entity definition
     * 
     * @return EntityDefinition|null
     */
    public function getById(string $entityDefinitionId, string $accountId): ?EntityDefinition
    {
        $def = $this->dataMapper->fetchById($entityDefinitionId, $accountId);
        $objType = $def->getObjType();
        if ($this->isLoaded($objType)) {
            return $this->loadedDefinitions[$objType];
        }

        return $this->loadDefinition($objType, $accountId);
    }

    /**
     * Save a defintion from a system definition if it exists
     *
     * @param string $objType The object type of the entity definition we are going to reset
     * @param string $accountId The account that owns the entity definition
     */
    public function forceSystemReset(string $objType, string $accountId)
    {
        $sysData = $this->getSysDef($objType);
        $def = $this->loadDefinition($objType, $accountId);

        // Check the revision to see if we need to update
        if ($sysData) {
            // System definition has been updated, save to datamapper
            $def->fromArray($sysData);
            $this->dataMapper->save($def);
            $this->clearCache($objType);
        }
    }

    /**
     * Check if an entity definition exists
     *
     * @param string $objType The object type of the entity definition we are loading
     * @param string $accountId The account that owns the entity definition
     * 
     * @return bool
     */
    public function definitionExists(string $objType, string $accountId): bool
    {
        try {
            $def = $this->get($objType, $accountId);
            return ($def) ? true : false;
        } catch (\Exception $exception) {
            return false;
        }
    }

    /**
     * Construct the definition class
     *
     * @param string $objType The object type of the entity definition we are loading
     * @param string $accountId The account that owns the entity definition
     * 
     * @return EntityDefinition|null
     */
    private function loadDefinition(string $objType, string $accountId)
    {
        // No cache, then load from dataMapper
        if (!$def) {
            $def = $this->dataMapper->fetchByName($objType, $accountId);
        }

        // Does not exist
        if (!$def) {
            return null;
        }

        // Load system views
        $this->setSysViews($def);

        // Load system forms
        // TODO: We are removing forms from the definition to clean-up
        //        And now we should use the Entity\Form service
        $this->setSysForms($def);

        // Load system aggregates
        $this->setSysAggregates($def);

        // Cache the loaded definition for future requests
        $this->loadedDefinitions[$objType] = $def;
        $this->cache->set($this->getUniqueKeyForObjType($objType, $accountId), $def->toArray());

        return $def;
    }

    /**
     * Generate a unique key for an object type for key/value caching
     *
     * @param string $objType
     * @return string
     */
    private function getUniqueKeyForObjType(string $objType, string $accountId)
    {
        $hash = $this->dataMapper->getLatestSystemDefinitionHash($objType);
        
        if (!$hash) {
            $hash = 'custom';
        }

        return "$accountId/entitydefinition/$hash-$objType";
    }


    /**
     * Check to see if the entity has already been loaded
     *
     * @param string $key The unique key of the loaded object
     * @return boolean
     */
    private function isLoaded(string $key)
    {
        $loaded = isset($this->loadedDefinitions[$key]);

        return $loaded;
    }

    /**
     * Check to see if an entity is cached
     *
     * @param string $objType The unique name of the object to that was cached
     * @param string $accountId Account of the definition we are loading from cache
     * 
     * @return EntityDefinition|bool EntityDefinition if found in cache, false if not cached
     */
    private function getCached(string $objType, string $accountId)
    {
        // Load the cache DataMapper and put it into $this->loadedEntities
        $ret = $this->cache->get($this->getUniqueKeyForObjType($objType, $accountId));

        if ($ret) {
            $def = new EntityDefinition($objType, $accountId);
            $def->fromArray($ret);
            return $def;
        }

        return false;
    }

    /**
     * Load the definition from the filesystem to see if it has been updated since our last asve
     *
     * @param string $objType The name of the object to pull
     * @return array|false Array if found, false if not a system object with a definition
     */
    private function getSysDef($objType)
    {
        $ret = false;

        // Check for system object        
        $basePath = $this->config->application_path . "/data/entity_definitions";
        if (file_exists($basePath . "/" . $objType . ".php")) {
            $ret = include($basePath . "/" . $objType . ".php");

            if (is_array($ret['fields'])) {
                foreach ($ret['fields'] as $fieldName => $fld) {
                    $ret['fields'][$fieldName]["system"] = true;
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
                foreach ($ret['aggregates'] as $aggData) {
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

    /**
     * Set system views
     *
     * @param EntityDefinition $def
     * @return int|bool Number of views on success, false on failure
     */
    private function setSysViews($def)
    {
        $objType = $def->getObjType();

        if (!$objType) {
            return false;
        }

        $numViews = 0;

        // Check for system object        
        $basePath = $this->config->application_path . "/data/browser_views";
        if (file_exists($basePath . "/" . $objType . ".php")) {
            $viewsData = include($basePath . "/" . $objType . ".php");

            foreach ($viewsData as $viewData) {
                $view = new BrowserView();
                $view->fromArray($viewData);
                $def->addView($view);
                $numViews++;
            }
        }

        return $numViews;
    }

    /**
     * Set system UIXML forms for displaying objects
     *
     * @param EntityDefinition $def
     */
    private function setSysForms($def)
    {
        $objType = $def->getObjType();

        if (!$objType) {
            return;
        }

        // Check for system object
        $basePath = $this->config->application_path . "/data";
        if (file_exists($basePath . "/entity_forms/" . $objType . "/default.php")) {
            $xml = file_get_contents($basePath . "/entity_forms/" . $objType . "/default.php");
            if ($xml) {
                $def->setForm($xml, "default");
            }
        }

        if (file_exists($basePath . "/entity_forms/" . $objType . "/mobile.php")) {
            $xml = file_get_contents($basePath . "/entity_forms/" . $objType . "/mobile.php");
            if ($xml) {
                $def->setForm($xml, "mobile");
            }
        }

        if (file_exists($basePath . "/entity_forms/" . $objType . "/infobox.php")) {
            $xml = file_get_contents($basePath . "/entity_forms/" . $objType . "/infobox.php");
            if ($xml) {
                $def->setForm($xml, "infobox");
            }
        }
    }

    /**
     * Clear cache
     *
     * @param string $objType The object type name
     * @param string $accountId Account of the definitions we are clearing from cache
     */
    public function clearCache(string $objType, string $accountId)
    {
        $this->loadedDefinitions[$objType] = null;
        $this->cache->remove($this->getUniqueKeyForObjType($objType, $accountId));

        // Remove cached all Object Types
        $this->cache->remove("$accountId/objects/allObjectTypes");
    }

    /**
     * Get object list blank state html
     *
     * This is set when the object definition loads
     *
     * @return string The html of the message to be preseted to the user when a list is blank
     */
    public function getBrowserBlankContent($objType)
    {
        $html = "<div class='aobListBlankState'>No items found</div>";

        if (file_exists(dirname(__FILE__) . "/../objects/olbstate/" . $objType . ".php")) {
            $html = file_get_contents(dirname(__FILE__) . "/../objects/olbstate/" . $objType . ".php");
        }

        return $html;
    }

    /**
     * Load all the definitions
     * 
     * @param string $accountId Account of the definitions we are loading from cache
     * 
     * @return EntityDefinition[]
     */
    public function getAll(string $accountId)
    {
        // First try to load the definitions from cache
        $allObjectTypes = $this->cache->get("$accountId/objects/allObjectTypes");

        // No cache, then load objects from dataMapper
        if (!$allObjectTypes) {
            $allObjectTypes = $this->dataMapper->getAllObjectTypes($accountId);

            // Cache the loaded objects for future requests
            $this->cache->set("$accountId/objects/allObjectTypes", $allObjectTypes);
        }

        $ret = [];
        foreach ($allObjectTypes as $objType) {
            // Get the defintion of the current $objType
            $ret[] = $this->get($objType, $accountId);
        }

        return $ret;
    }

    /**
     * Save a definition using the datamapper
     *
     * @param EntityDefinition $def The definition to save
     * @return string|bool entity id on success, false on failure
     */
    public function save($def)
    {
        $ret = $this->dataMapper->save($def);
        $this->clearCache($objType);

        return $ret;
    }

    /**
     * Delete a definition using the detamapper
     *
     * @param EntityDefinition $def The definition to delete
     * @return bool true on success, false on failure
     */
    public function delete($def)
    {
        $ret = $this->dataMapper->delete($def);
        $this->clearCache($objType);

        return $ret;
    }
}
