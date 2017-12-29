<?php
/**
 * A DataMapper is responsible for writing and reading data from a persistant store
 *
 * TODO: we are currently porting this over to v4 framework from v3
 * So far it has just been copied and the namespace replaced the prefix name
 *
 * @category	DataMapper
 * @author		Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright	Copyright (c) 2003-2013 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntityDefinition\DataMapper;

use Netric\EntityDefinition\EntityDefinition;

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
		$this->deleteDef($def);

		// Clear cache
		$this->getLoader()->clearCache($def->getObjType());
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
        $def->revision++;

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
        return $this->getAccount()->getServiceManager()->get("EntityDefinitionLoader");
	}

	/**
	 * Get data for a grouping field (fkey)
	 *
	 * @param string $objType The object type name we are working with 
	 * @param string $fieldName the name of the grouping(fkey, fkey_multi) field 
	 * @param array $conditions Array of conditions used to slice the groupings
	 * @param string $parent the parent id to query for subvalues
	 * @param string $nameValue namevalue to query for a single grouping by name
	 * @return array of grouping in an associate array("id", "title", "viewname", "color", "system", "children"=>array)
	 */
	public function getGroupings($objType, $fieldName, $filter=array())
	{
		$def = $this->getLoader()->get($objType);
		if (!$def)
			return false;

		$field = $def->getField($fieldName);
		if (!$field)
			return false;

		$data = $this->getGroupingsData($def, $field, $filter);
	} 
}