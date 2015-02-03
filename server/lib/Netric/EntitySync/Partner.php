<?php
/**
 * Sync partner represents a foreign datastore and/or device to import and export data to
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */

namespace Netric\EntitySync;

/**
 * Class used to represent a sync partner or endpoint
 */
class Partner
{
	/**
	 * DataMapper handle
	 *
	 * @var Netric\EntitySync\DataMapperInterface
	 */
	private $dataMapper = null;

	/**
	 * Internal unique identifier for this partnership
	 *
	 * @var int
	 */
	public $id = null;

	/**
	 * The netric user who owns this partnership
	 *
	 * @var int
	 */
	public $ownerId = null;

	/**
	 * Partner id which is a foreign id but must be unique
	 *
	 * Mobile devices send unique identifiers like "iphone-43342543543..."
	 *
	 * @var string
	 */
	public $partnerId = null;

	/**
	 * Last sync time
	 *
	 * @var string
	 */
	public $lastSync = null;

	/**
	 * Object collections this partner is listening for
	 *
	 * For example: 'customer','task' would mean the partner is
	 * only tracking changes for objects of type customer and task
	 * but will ignore all others. This will keep overhead to a minimal
	 * when tracking changes. In additional collections can have filters
	 * allowing synchronization of a subset of data.
	 *
	 * @var Netric\EntitySync\Collection\CollectionInterface[]
	 */
	public $collections = array();

	/**
	 * Class constructor
	 *
	 * @param CDatabase $dbh Handle to account database
	 * @param string $partnerId The unique id of this partnership
	 * @param AntUser $user Current user object
	 */
	public function __construct(
		\Netric\EntitySync\DataMapperInterface $syncDm, 
		$partnerId = null
	)
	{
		$this->dataMapper = $syncDm;
		$this->partnerId = $partnerId;
	}

	/**
	 * Check to see if this partnership is listening for changes for a specific type of object
	 *
	 * @param string $obj_type The name of the object type to check
	 * @param string $fieldName Name of a field if this is a grouping collection
	 * @param array $conditions Array of conditions used to filter the collection
	 * @param bool $addIfMissing Add the object type to the list of synchronized objects if it does not already exist
	 * @return AntObjectSync_Collection|bool collection on found, false if none found
	 */
	public function getEntityCollection($obj_type, $conditions=array(), $addIfMissing=false)
	{
	}

	/**
	 * Check to see if this partnership is listening for changes for a grouping field
	 *
	 * @param string $obj_type The name of the object type to check
	 * @param string $fieldName Name of a field if this is a grouping collection
	 * @param array $conditions Array of conditions used to filter the collection
	 * @param bool $addIfMissing Add the object type to the list of synchronized objects if it does not already exist
	 * @return AntObjectSync_Collection|bool collection on found, false if none found
	 */
	public function getGroupingCollection($obj_type, $fieldName=null, $conditions=array(), $addIfMissing=false)
	{
	}

	/**
	 * Check to see if this partnership is listening for changes for a specific type of object
	 *
	 * @param array $conditions Array of conditions used to filter the collection
	 * @param bool $addIfMissing Add the object type to the list of synchronized objects if it does not already exist
	 * @return AntObjectSync_Collection|bool collection on found, false if none found
	 */
	public function getEntityDefintionCollection($conditions=array(), $addIfMissing=false)
	{
	}

	/**
	 * Check to see if this partnership is listening for changes for a specific type of object
	 *
	 * @param string $obj_type The name of the object type to check
	 * @param string $fieldName Name of a field if this is a grouping collection
	 * @param array $conditions Array of conditions used to filter the collection
	 * @param bool $addIfMissing Add the object type to the list of synchronized objects if it does not already exist
	 * @return AntObjectSync_Collection|bool collection on found, false if none found
	 */
	private function getCollection($obj_type, $fieldName=null, $conditions=array(), $addIfMissing=false)
	{
		$ret = false;
		if (!is_array($conditions))
			$conditions = array();

		foreach ($this->collections as $col)
		{
			if (!is_array($col->conditions))
				$col->conditions = array();

			if ($obj_type == $col->objectType && count($conditions) == count($col->conditions))
			{
				if ($fieldName)
				{
					if ($fieldName == $col->fieldName)
						$ret = $col;
				}
				else if (!$col->fieldName)
				{
					$ret = $col;
				}

				// Make sure conditions match - if not set back to false
				if ($ret!=false && count($conditions) > 0)
				{
					// Compare against challenge list
					foreach ($conditions as $cond)
					{
						$found = false;
						foreach ($col->conditions as $cmdCond)
						{
							if ($cmdCond['blogic'] == $cond['blogic'] 
								&& $cmdCond['field'] == $cond['field'] 
								&& $cmdCond['operator'] == $cond['operator'] 
								&& $cmdCond['condValue'] == $cond['condValue'])
							{
								$found = true;
							}
						}

						if (!$found)
						{
							$ret = false;
							break;
						}
					}

					// Compare against collection conditions
					foreach ($col->conditions as $cond)
					{
						$found = false;
						foreach ($conditions as $cmdCond)
						{
							if ($cmdCond['blogic'] == $cond['blogic'] 
								&& $cmdCond['field'] == $cond['field'] 
								&& $cmdCond['operator'] == $cond['operator'] 
								&& $cmdCond['condValue'] == $cond['condValue'])
							{
								$found = true;
							}
						}

						if (!$found)
						{
							$ret = false;
							break;
						}
					}
				}
			}
		}

		if (!$ret && $addIfMissing)
			$ret = $this->addCollection($obj_type, $fieldName, $conditions);

		return $ret;
	}

	/**
	 * Add an object type to the list of synchronized objects for this partnership
	 *
	 * @param string $obj_type The name of the object type to check
	 * @param string $fieldName optional field name of synchronizing grouping fields
	 * @param CAntObjectCond[] $conditions Array of conditions used to filter the collection
	 * @return array|bool entity associative array if the partner is listening, false if it should ignore this object type
	 */
	public function addCollection($obj_type, $fieldName=null, $conditions=array())
	{
		// Make sure we are not already listening
		$ret = $this->getCollection($obj_type, $fieldName);
		if ($ret)
			return $ret;

		$odef = CAntObject::factory($this->dbh, $obj_type);
		$fieldId = null;
		if ($fieldName)
		{
			$field = $odef->fields->getField($fieldName);
			$fieldId = $field['id'];
		}

		$col = new AntObjectSync_Collection($this->dbh, null, $this->user);
		$col->objectType = $obj_type;
		$col->objectTypeId = $odef->object_type_id;
		$col->fieldName = $fieldName;
		$col->fieldId = $fieldId;
		$col->conditions = $conditions;
		if ($this->id)
		{
			$col->partnerId = $this->id;
			$col->save();
		}
		$this->collections[] = $col;

		return $col;
	}

	/**
	 * Clear all collections for this partner
	 * 
	 * Note: Calling code must save the partnership to cause the clearing to persist.
	 */
	public function removeCollections()
	{	
		$this->collections = array();
	}
}
