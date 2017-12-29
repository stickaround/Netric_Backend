<?php

/**
 * Handle loading object definitions
 *
 * @category  Entity
 * @package   DefinitionLoader
 * @copyright Copyright (c) 2003-2013 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric;

use Netric\Cache\CacheInterface;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Entity\BrowserView\BrowserView;

/**
 * Class to handle to loading of object definitions
 */
class EntityDefinitionLoader
{
	/**
	 * The current data mapper we are using for this object
	 * 
	 * @var DataMapperInterface
	 */
	protected $dataMapper = null;

	/**
	 * Array of loaded entities
	 * 
	 * @var array
	 */
	private $loadedDefinitions = array();

	/**
	 * Cache
	 *
	 * @var \Netric\Cache\CacheInterface
	 */
	private $cache = null;

	/**
	 * Setup IdentityMapper for loading objects
	 * 
	 * @param DataMapperInterface $dm Datamapper for entity definitions
	 * @param CacheInterface $cache Optional cache object
	 * @return EntityDefinitionLoader
	 */
	public function __construct($dm, Cache\CacheInterface $cache = null)
	{
		$this->cache = $cache;
		$this->dataMapper = $dm;
		return $this;
	}

	/**
	 * Get an entity
	 * 
	 * @param string $objType
	 * @return Entity
	 */
	public function get($objType)
	{
		if (!$objType || !is_string($objType))
			throw new \Exception('ObjType Paramater is required');

		if ($this->isLoaded($objType)) {
			return $this->loadedDefinitions[$objType];
		} else {
			return $this->loadDefinition($objType);
		}
	}

	/**
	 * Construct the definition class
	 *
	 * @param string $objType
	 * @return EntityDefinition|null
	 */
	private function loadDefinition($objType)
	{
		// First try to load from cache
		$def = $this->getCached($objType);

		// No cache, then load from dataMapper
		if (!$def)
			$def = $this->dataMapper->fetchByName($objType);

		// Does not exist
		if (!$def)
			return null;

		// Check if this is a system object and if it is 
		$sysData = $this->getSysDef($objType);

		// Check the revision to see if we need to update
		if ($sysData) {
			if ((int)$sysData['revision'] > $def->revision) {

				// System definition has been updated, save to datamapper
				$def->fromArray($sysData);
				$this->dataMapper->save($def);
			} else {
				// Set custom code level variables not in the dataMapper
				if (isset($sysData["default_activity_level"]))
					$def->defaultActivityLevel = $sysData["default_activity_level"];

				if (isset($sysData["is_private"]))
					$def->isPrivate = $sysData["is_private"];

				if (isset($sysData["store_revisions"]))
					$def->storeRevisions = $sysData["store_revisions"];

				if (isset($sysData["recur_rules"]))
					$def->recurRules = $sysData["recur_rules"];

				if (isset($sysData["inherit_dacl_ref"]))
					$def->inheritDaclRef = $sysData["inherit_dacl_ref"];

				if (isset($sysData["parent_field"]))
					$def->parentField = $sysData["parent_field"];

				if (isset($sysData["uname_settings"]))
					$def->unameSettings = $sysData["uname_settings"];

				if (isset($sysData["list_title"]))
					$def->listTitle = $sysData["list_title"];

				if (isset($sysData["icon"]))
					$def->icon = $sysData["icon"];
			}
		}

		// Load system views
		$this->setSysViews($def);

		// Load system forms
		// TODO: We are removing forms from the definition to clean-up
		// 		  And now we should use the Entity\Form service
		$this->setSysForms($def);

		// Load system aggregates
		$this->setSysAggregates($def);

		// Cache the loaded definition for future requests
		$this->loadedDefinitions[$objType] = $def;
		$this->cache->set($this->dataMapper->getAccount()->getId() . "/objects/" . $objType, $def->toArray());

		return $def;
	}

	/**
	 * Save a defintion from a system definition if it exists
	 *
	 * @param string $objType 
	 */
	public function forceSystemReset($objType)
	{
		$sysData = $this->getSysDef($objType);
		$def = $this->loadDefinition($objType);

		// Check the revision to see if we need to update
		if ($sysData) {
			// Reset to the system revision
			$def->revision = $sysData['revision'];

			// System definition has been updated, save to datamapper
			$def->fromArray($sysData);
			$this->dataMapper->save($def);
		}
	}


	/**
	 * Check to see if the entity has already been loaded 
	 * 
	 * @param string $key The unique key of the loaded object
	 * @return boolean
	 */
	private function isLoaded($key)
	{
		$loaded = isset($this->loadedDefinitions[$key]);

		return $loaded;
	}

	/**
	 * Check to see if an entity is cached
	 *
	 * @param string $objType The unique name of the object to that was cached
	 * @return EntityDefinition|bool EntityDefinition if found in cache, false if not cached
	 */
	private function getCached($objType)
	{
        // Load the cache datamapper and put it into $this->loadedEntities
		$ret = $this->cache->get($this->dataMapper->getAccount()->getId() . "/objects/" . $objType);

		if ($ret) {
			$def = new EntityDefinition($objType);
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
		$config = $this->dataMapper->getAccount()->getServiceManager()->get("Config");
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
		$config = $this->dataMapper->getAccount()->getServiceManager()->get("Config");
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

	/**
	 * Set system views
	 *
	 * @param EntityDefinition $def
	 * @return int|bool Number of views on success, false on failure
	 */
	private function setSysViews(&$def)
	{
		$objType = $def->getObjType();

		if (!$objType)
			return false;

		$numViews = 0;

		// Check for system object
		$config = $this->dataMapper->getAccount()->getServiceManager()->get("Config");
		$basePath = $config->application_path . "/data/browser_views";
		if (file_exists($basePath . "/" . $objType . ".php")) {
			$viewsData = include($basePath . "/" . $objType . ".php");

			foreach ($viewsData as $viewName => $viewData) {
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
	private function setSysForms(&$def)
	{
		$objType = $def->getObjType();

		if (!$objType)
			return;

		$numViews = 0;

		// Check for system object
		$basePath = $this->dataMapper->getAccount()->getServiceManager()->get("Config")->application_path . "/data";
		if (file_exists($basePath . "/entity_forms/" . $objType . "/default.php")) {
			$xml = file_get_contents($basePath . "/entity_forms/" . $objType . "/default.php");
			if ($xml)
				$def->setForm($xml, "default");
		}

		if (file_exists($basePath . "/entity_forms/" . $objType . "/mobile.php")) {
			$xml = file_get_contents($basePath . "/entity_forms/" . $objType . "/mobile.php");
			if ($xml)
				$def->setForm($xml, "mobile");
		}

		if (file_exists($basePath . "/entity_forms/" . $objType . "/infobox.php")) {
			$xml = file_get_contents($basePath . "/entity_forms/" . $objType . "/infobox.php");
			if ($xml)
				$def->setForm($xml, "infobox");
		}
	}

	/**
	 * Clear cache
	 *
	 * @param string $objType The object type name
	 */
	public function clearCache($objType)
	{
		$this->loadedDefinitions[$objType] = null;
		$ret = $this->cache->remove($this->dataMapper->getAccount()->getId() . "/objects/" . $objType);

		// Remove cached all Object Types
		$this->cache->remove($this->dataMapper->getAccount()->getId() . "/objects/allObjectTypes");
	}

	/**
	 * Get object list blank state html
	 *
	 * This is set when the object definition loads
	 *
	 * @return string The html of the message to be preseted to the user when a list is blank
	 */
	public function getBrowserBlankContent($objType, $scope = "default")
	{
		if (file_exists(dirname(__FILE__) . "/../objects/olbstate/" . $objType . ".php")) {
			$html = file_get_contents(dirname(__FILE__) . "/../objects/olbstate/" . $objType . ".php");
		} else {
			$html = "<div class='aobListBlankState'>No items found</div>";
		}

		return $html;
	}

	/**
	 * Load all the definitions
	 *
	 * @return array	Collection of object definitions
	 */
	public function getAll()
	{
		// First try to load the definitions from cache
		$allObjectTypes = $this->cache->get($this->dataMapper->getAccount()->getId() . "/objects/allObjectTypes");

		// No cache, then load objects from dataMapper
		if (!$allObjectTypes) {
			$allObjectTypes = $this->dataMapper->getAllObjectTypes();

			// Cache the loaded objects for future requests
			$this->cache->set($this->dataMapper->getAccount()->getId() . "/objects/allObjectTypes", $allObjectTypes);
		}

		$ret = array();
		foreach ($allObjectTypes as $objType) {

			// Get the defintion of the current $objType
			$ret[] = $this->get($objType);
		}

		return $ret;
	}
}
