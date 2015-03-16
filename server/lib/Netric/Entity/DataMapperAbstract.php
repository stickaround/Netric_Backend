<?php
/**
 * A DataMapper is responsible for writing and reading data from a persistant store
 *
 * @category	DataMapper
 * @author		Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright	Copyright (c) 2003-2013 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\Entity;

abstract class DataMapperAbstract extends \Netric\DataMapperAbstract
{
    /**
     * The type of object this data mapper is handling
     * 
     * @var string
     */
    protected $objType = "";

	/**
	 * Record of moved-to references
	 *
	 * @var arrray
	 */
	 protected $movedToRef = array();

	 /**
	  * Commit manager used to crate global commits for sync
	  *
	  * @var \Netric\Entity\Commit\Manager
	  */
	 protected $commitManager = null;

	/**
	 * Class constructor
	 * 
	 * @param ServiceLocator $sl The ServiceLocator container
	 * @param string $accountName The name of the ANT account that owns this data
	 */
	public function __construct(\Netric\Account $account)
	{
		$this->setAccount($account);
		$this->setUp();

		$this->commitManager = $account->getServiceManager()->get("EntitySyncCommitManager");
		$this->entitySync = $account->getServiceManager()->get("EntitySync");
	}

    /**
	 * Get object definition based on an object type
	 *
     * @param string $objType The object type name
     * @param string $fieldName The field name to get grouping data for
	 * @return \Netric\\EntityGroupings
	 */
	//abstract public function getGroupings($objType, $fieldName, $filters=array());
    
    /**
     * Save groupings
     * 
     * @param \Netric\EntityGroupings
     * @param int $commitId The new commit id
     */
    abstract protected function _saveGroupings(\Netric\EntityGroupings $groupings, $commitId);

	/**
	 * Set this object as having been moved to another object
	 *
	 * @param EntityDefinition $def The defintion of this object type
	 * @param string $fromId The id to move
	 * @param stirng $toId The unique id of the object this was moved to
	 * @return bool true on succes, false on failure
	 */
	//abstract public function setEntityMovedTo(&$def, $fromId, $toId);

	/**
	 * The setup function is used by all derrived classes as constructors
	 */
	abstract protected function setUp();
    
	/**
	 * Open object by id
	 *
     * @var Entity $entity The entity to load data into
     * @var string $id The Id of the object
	 * @return bool true on success, false on failure
	 */
	abstract protected function fetchById(&$entity, $id);

	/**
	 * Purge data from the database
	 * 
     * @var Entity $entity The entity to load data into
	 * @return bool true on success, false on failure
	 */
	abstract protected function deleteHard(&$entity);

	/**
	 * Flag data as deleted or archive but don't actually delete it
	 * 
     * @var Entity $entity The entity to load data into
	 * @return bool true on success, false on failure
	 */
	abstract protected function deleteSoft(&$entity);

	/**
	 * Save object data
	 *
	 * @param Entity $entity The entity to save
	 * @return string|bool entity id on success, false on failure
	 */
	abstract protected function saveData($entity);

	/**
	 * Check if an object has moved
	 *
	 * @param Entity $entity 
	 * @param string $id The id of the object that no longer exists - may have moved
	 * @return string|bool New entity id if moved, otherwise false
	 */
	abstract protected function entityHasMoved($entity, $id);

	/**
	 * Save revision snapshot
	 *
	 * @param Entity $entity The entity to save
	 * @return string|bool entity id on success, false on failure
	 */
	abstract protected function saveRevision($entity);

	/**
	 * Get Revisions for this object
	 *
	 * @param string $objType The name of the object type to get
	 * @param string $id The unique id of the object to get revisions for
	 * @return array("revisionNum"=>Entity)
	 */
	abstract public function getRevisions($objType, $id);

	/**
	 * Save main processor
	 *
	 * @param Entity $entity The enitity to save
     * @param \Netric\Entity\ObjType\User $user Optional user performing the save if other than current in $this->account
	 */
	public function save($entity, $user=null)
	{
		// Increment revision for this save
		$revision = $entity->getValue("revision");
		if (!$revision)
			$revision = 1;
		else
			$revision++;
		$entity->setValue("revision", $revision);

		// Create new global commit revision
		$lastCommitId = $entity->getValue('commit_id');
		$commitId = $this->commitManager->createCommit("entities/" . $entity->getDefinition()->getObjType());
		$entity->setValue('commit_id', $commitId);

		// Call onBeforeSave
		if ($this->getAccount()->getServiceManager())
			$entity->onBeforeSave($this->getAccount()->getServiceManager());

		// Save data to DataMapper implementation
		$ret = $this->saveData($entity);

		// Save revision for historical reference
		if ($entity->getDefinition()->storeRevisions)
			$this->saveRevision($entity);

		// Save data to EntityCollection_Index
		//if ($this->getServiceLocator())
			//$this->getServiceLocator()->get("EntityCollection_Index")->save($entity);
		
		// Clear cache in the EntityLoader
		if ($this->getAccount()->getServiceManager())
			$this->getAccount()->getServiceManager()->get("EntityLoader")->clearCache($entity->getDefinition()->getObjType(), $entity->getId());
		
		// Log the change in entity sync
		if ($ret && $lastCommitId && $commitId)
		{
			$this->entitySync->setExportedStale(
				\Netric\EntitySync\EntitySync::COLL_TYPE_ENTITY, 
				$lastCommitId, $commitId);
		}

		// Call onAfterSave
		if ($this->getAccount()->getServiceManager())
			$entity->onAfterSave($this->getAccount()->getServiceManager());

		return $ret;
	}

	/**
	 * Get an entity by id
	 *
	 * @param Entity $entity The enitity to save
	 * @return bool true if found and loaded successfully, false if not found or failed
	 */
	public function getById(&$entity, $id)
	{
		$ret = $this->fetchById($entity, $id);

		if (!$ret)
		{
			$movedToId = $this->entityHasMoved($entity->getDefinition(), $id);
			if ($movedToId && $movedToId != $id)
				$ret = $this->fetchById($entity, $movedToId);
		}

		return $ret;
	}

	/**
	 * Delete an entity
	 *
	 * @param Entity $entity The enitity to save
	 * @param bool $forceHard If true the data will be purged, if false first it will be archived
	 * @return bool true on success, false on failure
	 */
	public function delete(&$entity, $forceHard=false)
	{
		$lastCommitId = $entity->getValue("commit_id");
		// Create new global commit revision
		$commitId = $this->commitManager->createCommit("entities/" . $entity->getDefinition()->getObjType());

		// Determine if we are flagging the entity as deleted or actually purging
		if ($entity->getValue("f_deleted") || $forceHard)
		{
			$ret = $this->deleteHard($entity);

			// Delete from EntityCollection_Index
			//if ($this->getServiceLocator())
				//$this->getServiceLocator()->get("EntityCollection_Index")->save($entity);
			
			// Remove unique DACL. Of course, we don't want to delete the dacl for all object types, just for this id
			//if ($this->daclIsUnique && $this->dacl)
				//$this->dacl->remove();
		}
		else
		{
			$entity->setValue('commit_id', $commitId);
			$ret = $this->deleteSoft($entity);

			// Delete from EntityCollection_Index
			//if ($this->getServiceLocator())
				//$this->getServiceLocator()->get("EntityCollection_Index")->delete($entity);
		}

		// Log the change in entity sync
		if ($ret && $lastCommitId && $commitId)
		{
			$this->entitySync->setExportedStale(
				\Netric\EntitySync\EntitySync::COLL_TYPE_ENTITY, 
				$lastCommitId, $commitId);
		}

		// Clear cache in the EntityLoader
		if ($this->getAccount()->getServiceManager())
			$this->getAccount()->getServiceManager()->get("EntityLoader")->clearCache($entity->getDefinition()->getObjType(), $entity->getId());
		
		return $ret;
	}
    
    /**
     * Save groupings
     * 
     * @param \Netric\EntityGroupings
     */
    public function saveGroupings(\Netric\EntityGroupings $groupings)
    {
    	// Increment head commit for groupings which triggers all collections to sync
		$commitHeadIdent = "groupings/" . $groupings->getObjType() . "/";
		$commitHeadIdent .= $groupings->getFieldName() . "/";
		$commitHeadIdent .= $groupings::getFiltersHash($groupings->getFilters());	

    	/*
		 * Groupings are all saved as a single collection, but only updated
		 * groupings will shre a new commit id.
		 */
		$nextCommit = $this->commitManager->createCommit($commitHeadIdent);

		// Save the grouping
        $log = $this->_saveGroupings($groupings, $nextCommit);

        /* No need to log changes because the sync function will get all newer commits
        foreach ($log['changed'] as $gid=>$lastCommitId)
        {
            // Log the change in entity sync
			if ($gid && $lastCommitId && $nextCommit)
			{
				$this->entitySync->setExportedStale(
					\Netric\EntitySync\EntitySync::COLL_TYPE_GROUPING, 
					$lastCommitId, $nextCommit);
			}
        }
        */
        
        foreach ($log['deleted'] as $gid=>$lastCommitId)
        {
            // Log the change in entity sync
			if ($gid && $lastCommitId && $nextCommit)
			{
				$this->entitySync->setExportedStale(
					\Netric\EntitySync\EntitySync::COLL_TYPE_GROUPING, 
					$lastCommitId, $nextCommit);
			}
        }
    }
        
	/**
	 * Make sure that a uname is still unique
	 *
	 * This should safe-gard against values being saved in the object that change the namespace
	 * of the unique name causing unique collision.
	 *
	 * @param Entity $entity The entity to save
	 * @param string $uname The name to test for uniqueness
	 * @param bool $reset If true then reset 'uname' field with new unique name
	 */
	public function verifyUniqueName($entity, $uname)
	{
		if (!$uname)
			return false;

		$def = $entity->getDefinition();

		// If we are not using unique names with this object just succeed
		if (!$def->unameSettings)
			return true;

		// TODO: we need to move this to collections but collections are not yet built
		// Search objects to see if the uname exists
		$olist = new CAntObjectList($this->dbh, $this->object_type, $this->user);
		$olist->addCondition("and", "uname", "is_equal", $uname);

		// Exclude this object from the query because of course it will be a duplicate
		if ($this->id)
			$olist->addCondition("and", "id", "is_not_equal", $this->id);

		// Loop through all namespaces if set with ':' in the settings
		$nsParts = explode(":", $def->unameSettings);
		if (count($nsParts) > 1)
		{
			// Use all but last, which is the uname field
			for ($i = 0; $i < (count($nsParts) - 1); $i++)
			{
				$olist->addCondition("and", $nsParts[$i], "is_equal", $this->getValue($nsParts[$i]));
			}
		}

		// Check if any objects match
		$olist->getObjects(0, 1);
		if ($olist->getNumObjects() > 0)
		{
			return false;
		}
		else
		{
			return true;
		}
	}
}
