<?php
/**
 * Sync collection
 *
 * @category  AntObjectSync
 * @package   Collection
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright Copyright (c) 2003-2015 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\EntitySync\Collection;

use Netric\EntitySync\Commit;

/**
 * Class used to represent a sync partner or endpoint
 */
class AbstractCollection
{
	/**
	 * DataMapper for sync operations
	 *
	 * @var \Netric\EntitySync\DataMapperInterface 
	 */
	protected $dataMapper = null;

	/**
	 * Service for managing commits
	 *
	 * @var \Netric\EntitySync\Commit\CommitManager 
	 */
	protected $commitManager = null;

	/**
	 * Internal id
	 *
	 * @var int
	 */
	protected $id = null;

	/**
	 * Partner id
	 *
	 * @var string
	 */
	protected $partnerId = null;

	/**
	 * Object type name
	 *
	 * @var string
	 */
	protected $objType = null;

	/**
	 * Object type name
	 *
	 * @var string
	 */
	protected $fieldName = null;

	/**
	 * Last sync time
	 *
	 * @var \DateTime
	 */
	protected $lastSync = null;

	/**
	 * Last commit id that was exported from this colleciton
	 * 
	 * @var string
	 */
	protected $lastCommitId = null;

	/**
	 * Conditions array
	 *
	 * @var array(array("blogic", "field", "operator", "condValue"));
	 */
    protected $conditions = array();

	/**
	 * Cache change results in a revision increment
	 *
	 * @var double
	 */
	protected $revision = 1;

	/**
	 * Last time this collection was checked for updates for mutiple subsequent calls
	 *
	 * @var float
	 */
	protected $lastRevisionCheck = null;

	/**
	 * Constructor
	 *
	 * @param \Netric\EntitySync\DataMapperInterface $dm The sync datamapper
	 */
	public function __construct(
		\Netric\EntitySync\DataMapperInterface $dm, 
		Commit\CommitManager $commitManager)
	{
		$this->dataMapper = $dm;
		$this->commitManager = $commitManager;
	}

	/**
	 * Set the last commit id synchronized
	 *
	 * @param string $commitId
	 */
	public function setLastCommitId($commitId)
	{
		$this->lastCommitId = $commitId;
	}

	/**
	 * Get the last commit ID that was syncrhonzied/exported from this collection
	 *
	 * @return string
	 */
	public function getLastCommitId()
	{
		return $this->lastCommitId;
	}

	/**
	 * Set the id of this collection
	 *
	 * @param string $id
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Get the unique id of this collection
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set the partner id of this collection
	 *
	 * @param string $pid
	 */
	public function setPartnerId($pid)
	{
		$this->partnerId = $pid;
	}

	/**
	 * Get the partner id of this collection
	 *
	 * @return string
	 */
	public function getPartnerId()
	{
		return $this->partnerId;
	}

	/**
	 * Set the object type if applicable
	 *
	 * @param string $objType
	 */
	public function setObjType($objType)
	{
		$this->objType = $objType;
	}

	/**
	 * Get the object type if applicable
	 *
	 * @return string
	 */
	public function getObjType()
	{
		return $this->objType;
	}

	/**
	 * Set the name of a grouping field if set
	 *
	 * @param string $fieldName Name of field to set
	 */
	public function setFieldName($fieldName)
	{
		$this->fieldName = $fieldName;
	}

	/**
	 * Get the name of a grouping field if set
	 *
	 * @return string
	 */
	public function getFieldName()
	{
		return $this->fieldName;
	}

	/**
	 * Set last sync timestamp
	 *
	 * @param \DateTime $timestamp When the partnership was last synchronized
	 */
	public function setLastSync(\DateTime $timestamp)
	{
		$this->lastSync = $timestamp;
	}

	/**
	 * Set the revision
	 *
	 * @param string $revision
	 */
	public function setRevision($revision)
	{
		$this->revision = $revision;
	}

	/**
	 * Get the revision
	 *
	 * @return string
	 */
	public function getRevision()
	{
		return $this->revision;
	}

	/**
	 * Set conditions with array
	 *
	 * @param array $conditions array(array("blogic", "field", "operator", "condValue"))
	 */
	public function setConditions($conditions)
	{
		$this->conditions = $conditions;
	}

	/**
	 * Get conditions
	 * 
	 * @return array(array("blogic", "field", "operator", "condValue"))
	 */
	public function getConditions()
	{
		return $this->conditions;
	}

	/**
	 * Get last sync timestamp
	 *
	 * @param string $strFormat If set format the DateTime object as a string and return
	 * @return DateTime|string $timestamp When the partnership was last synchronized
	 */
	public function getLastSync($strFormat=null)
	{
		// If desired return a formatted string version of the timestamp
		if ($strFormat && $this->lastSync)
		{
			return $this->lastSync->format($strFormat);
		}

		return $this->lastSync;
	}

	/**
	 * Log that a commit was exported from this collection
	 * 
	 * @param int $uniqueId The unique id of the object we sent
	 * @param int $commitId The unique id of the commit we sent
	 */
	public function logExported($uniqueId, $commitId)
	{
		if (!$this->getId())
			return false;

		return $this->dataMapper->logExported($this->getType(), $this->getId(), $uniqueId, $commitId);
	}

	/**
	 * Get a list of previously exported commits that have been updated
	 *
	 * This is used to get a list of objects that were previously synchornized
	 * but were later either moved outside the collection (no longer met conditions)
	 * or deleted.
	 *
	 * NOTE: THIS MUST BE RUN AFTER GETTING NEW/CHANGED OBJECTS IN A COLLECTION.
	 * 	1. Get all new commits from last_commit and log the export
	 * 	2. Once all new commit updates were retrieved for a collection then call this
	 *  3. Once this returns empty then fast-forward this collection to head
	 *
	 * @return array(array('id'=>objectId, 'action'=>'delete'))
	 */
	public function getExportedStale()
	{
		if (!$this->getId())
			return array();

		$staleStats = array();

		$stale = $this->dataMapper->getExportedStale($this->getId());
		foreach ($stale as $oid)
		{
			$staleStats[] = array(
				"id" => $oid,
				"action" => 'delete',
			);
		}

		return $staleStats;
	}


	// LEGACY BELOW
	// -------------------------------------------------

	/**
	 * Test whether a referenced object matches filter conditions for this collection
	 *
	 * @param CAntObject $obj
	 * @return bool true of conditions match, false if they fail
	 */
	public function conditionsMatchObj($obj)
	{
		if (!$obj->id)
			return false; // only saved objects allowed because we use object list to build the query condition

		$pass = true;

		if (count($this->conditions))
		{
			$pass = false; // now assume fail because we need to meet filter conditions
			$olist = new CAntObjectList($this->dbh, $obj->object_type);
			$olist->addCondition("and", "id", "is_equal", $obj->id);
			if ('t' == $obj->getValue("f_deleted"))
				$olist->addCondition("and", "f_deleted", "is_equal", 't');

			foreach ($this->conditions as $cond)
			{
				// If we are working with hierarchy then we need to use is_less_or_equal operator
				// to include children in the query.
				if ($cond['field'] == $obj->fields->parentField && $cond['operator'] == "is_equal")
					$cond['operator'] = "is_less_or_equal";

				$olist->addCondition($cond['blogic'], $cond['field'], $cond['operator'], $cond['condValue']);
			}

			// Run query and see if object meets conditions
			$olist->getObjects(0, 1);

			if ($olist->getNumObjects() == 1)
				$pass = true;
		}

		return $pass;
	}

	/**
	 * Save the stat params for an imported object
	 *
	 * @parm string $devid Unique device id
	 * @param string $uid The unique id of the remove object
	 * @param int $revision The remote revision of the object when saved
	 * @param int $oid The object id to update
	 * @param int $parentId If set, pull all objects that are a child of the parent id only
	 */
	public function updateImportObjectStat($uid, $revision, $oid=null, $parentId=null)
	{
		// First remove if already exists
		$this->dbh->Query("DELETE FROM object_sync_import WHERE collection_id='" . $this->id . "' 
							AND unique_id='" . $this->dbh->Escape($uid) . "' and field_id is NULL");


		if ($oid)
		{
			if (!$this->objDef)
				$this->objDef = CAntObject::factory($this->dbh, $this->objectType);

			// Frist remove from stat outgoing table if already entered like object just saved before updating the import stat
			$sql = "DELETE FROM object_sync_stats WHERE collection_id='" . $this->id . "' 
					AND object_id='" . $oid . "' 
					AND object_type_id='" . $this->objDef->object_type_id . "'
					AND revision=".$this->dbh->EscapeNumber($revision)."";
			if ($parentId)
				$sql .= " AND parent_id=".$this->dbh->EscapeNumber($parentId)."";
			$this->dbh->Query($sql);

			// Now insert import stat
			$sql = "INSERT INTO object_sync_import(collection_id, object_type_id, object_id, unique_id, revision, parent_id)
								VALUES('" . $this->id . "', '" . $this->objectTypeId . "', 
										'" . $oid . "', '" . $this->dbh->Escape($uid) . "', 
										".$this->dbh->EscapeNumber($revision).", 
										".$this->dbh->EscapeNumber($parentId).");";

			$ret = $this->dbh->Query($sql);
		}
		else 
		{
			// if oid is null then do nothing, the $uid has been deleted from imported stats
			// because it no longer is represented by a local object id
			$ret = true;
		}

		if ($ret === false)
			return false;
		else
			return true;
	}

	/**
	 * Delete an imported object stat
	 *
	 * @parm string $devid Unique device id
	 * @param string $uid The unique id of the remove object
	 * @param int $revision The remote revision of the object when saved
	 * @param int $oid The object id to update
	 * @param int $parentId If set, pull all objects that are a child of the parent id only
	 */
	public function deleteImportObjectStat($uid, $oid=null, $parentId=null)
	{
		// First remove if already exists
		$this->dbh->Query("DELETE FROM object_sync_import WHERE collection_id='" . $this->id . "' 
							AND unique_id='" . $this->dbh->Escape($uid) . "' and field_id is NULL");


		if ($oid)
		{
			if (!$this->objDef)
				$this->objDef = CAntObject::factory($this->dbh, $this->objectType);

			// Frist remove from stat outgoing table if already entered like object just saved before updating the import stat
			$sql = "DELETE FROM object_sync_stats WHERE collection_id='" . $this->id . "' 
					AND object_id='" . $oid . "' AND object_type_id='" . $this->objDef->object_type_id . "' ";
			if ($parentId)
				$sql .= " AND parent_id=".$this->dbh->EscapeNumber($parentId)."";
			$this->dbh->Query($sql);
		}
		else 
		{
			// if oid is null then do nothing, the $uid has been deleted from imported stats
			// because it no longer is represented by a local object id
			$ret = true;
		}

		if ($ret === false)
			return false;
		else
			return true;
	}

	/**
	 * Get stats array with a diff from the previous import for objects in this collection
	 *
	 * @param array $importList Array of arrays with the following param for each object {uid, revision}
	 * @param int $parentId If set, pull all objects that are a child of the parent id only
	 * @return array(array('uid', 'object_id', 'action', 'revision');
	 */
	public function importObjectsDiff($importList, $parentId=null)
	{
		$changes = array();

		// Get previously imported list
		// --------------------------------------------------------------------
		$sql = "SELECT unique_id, object_id, revision FROM object_sync_import WHERE collection_id='" . $this->id . "'";
		if (is_numeric($parentId))
			$sql .= " AND parent_id='$parentId'";
		$result = $this->dbh->Query($sql);
		$num = $this->dbh->GetNumberRows($result);
		for ($i = 0; $i < $num; $i++)
		{
			$row = $this->dbh->GetRow($result, $i);
			
			// Mark all local to be deleted unless still exists in the imported list
			$changes[] = array(
				'uid' => $row['unique_id'],
				'object_id' => $row['object_id'],
				'revision' => $row['revision'],
				'action' => 'delete',
			);
		}
		
		// Loop through both lists and look for differences
		// --------------------------------------------------------------------
		foreach ($importList as $item)
		{
			$found = false;

			// Check existing
			for ($i = 0; $i < count($changes); $i++)
			{
				if ($changes[$i]['uid'] == $item['uid'])
				{
					if ($changes[$i]['revision'] == $item['revision'])
					{
						array_splice($changes, $i, 1); // no changes, remove
					}
					else
					{
						$changes[$i]['action'] = 'change'; // was updated on remote source
						$changes[$i]['revision'] = $item['revision'];
					}

					$found = true;
					break;
				}
			}

			if (!$found) // not found locally or revisions do not match
			{
				$changes[] = array(
					"uid" => $item['uid'], 
					"object_id" => $item['object_id'], 
					"revision" => $item['revision'], 
					"action" => "change",
				);
			}
		}

		return $changes;
	}

	/**
	 * Import groupings from device, keep history for incremental changes
	 *
	 * Sync local groupings with list from remote device. We do not need to export changes
	 * because that is handled real time with the device stat table as objects are updated.
	 *
	 * @param string[] $groupList Array of all groupings from the device
	 * @param string $delimiter Hierarchical delimiter to use when parsing groups
	 * @return array of assoiative array [["id"=><grouping.id>, "action"=>'change'|'delete']]
	 */
	public function importGroupingDiff($groupList, $delimiter='/')
	{
		$changed = array();
		$fieldName = $this->fieldName;
		$odef = ($objDef) ? $objDef : CAntObject::factory($this->dbh, $this->objectType, null, $this->user);

		// Leave if no field name or groups to sync
		if (!$fieldName || !is_array($groupList))
			return $changed;

		$field = $odef->fields->getField($fieldName);
		if (!is_array($field))
			return $changed;

		// Get previously imported list
		// --------------------------------------------------------------------
		$local = array();
		$result = $this->dbh->Query("SELECT unique_id FROM object_sync_import WHERE collection_id='" . $this->id . "'
									 AND field_id='".$field['id']."'");
		$num = $this->dbh->GetNumberRows($result);
		for ($i = 0; $i < $num; $i++)
			$local[] = $this->dbh->GetValue($result, $i, "unique_id");

		// Mark all local to be deleted unless still exists in the imported list
		foreach ($local as $lpath)
			$changed[] = array("unique_id"=>$lpath, "action"=>"delete");
		unset($local);

		// Loop through both lists and look for differences
		// --------------------------------------------------------------------
		foreach ($groupList as $grpName) 
		{
			$found = false;

			// Check existing
			for ($i = 0; $i < count($changed); $i++)
			{
				if ($changed[$i]['unique_id'] == $grpName)
				{
					array_splice($changed, $i, 1);
					$found = true;
					break;
				}
			}

			if (!$found) // not found locally
				$changed[] = array("unique_id"=>$grpName, "action"=>"change");
		}

		$odef->skipObjectSyncStat = true; // Do no sync changes up after importing thus creating an endless loop

		// Save new list to import
		// --------------------------------------------------------------------
		foreach ($changed as $ch)
		{
			// Translate hierarchical path
			if ($delimiter != "/") 
			   $grpPath = str_replace($delimiter, "/", $ch['unique_id']);
			else
				$grpPath = $ch['unique_id'];

			switch ($ch['action'])
			{
			case 'delete':
				$this->dbh->Query("DELETE FROM object_sync_import WHERE collection_id='" . $this->id . "'
									AND field_id='".$field['id']."' AND unique_id='" . $this->dbh->Escape($ch['unique_id']) . "'");

				// Delete grouping if it exists
				$grp = $odef->getGroupingEntryByPath($fieldName, $grpPath);
				if (is_array($grp) && $grp['id'])
					$odef->deleteGroupingEntry($fieldName, $grp['id']);

				break;
			case 'change':
				$this->dbh->Query("INSERT INTO object_sync_import(collection_id, object_type_id, field_id, unique_id) 
								   VALUES(
										'" . $this->id . "',
										'" . $this->objectTypeId. "',
										'" . $field['id'] . "',
										'" . $this->dbh->Escape($ch['unique_id']) . "'
								   );");

				// Add grouping if not exists
				$grp = $odef->getGroupingEntryByPath($fieldName, $grpPath);
				if (!$grp)
					$odef->addGroupingEntry($fieldName, $grpPath);
				break;
			}
		}

		$odef->skipObjectSyncStat = false; // Turn sync back on

		return $changed;
	}

	/**
	 * Determin if this collection has any changes to sync
	 *
	 * This is used to decrease performance load. It is especially useful for
	 * hierarchy collections like file systems because a call to the root parent
	 * will indicate the change status of all children as well.
	 */
	public function changesExist()
	{
		if (!$this->id)
			return false;

		if (!$this->fInitialized)
			$this->initObjectCollection();

		// This is a subsequent call, use cache to check if another process has updated and limit db queries
		if ($this->lastRevisionCheck)
		{
			$currentRevision = $this->cache->get($this->dbh->accountId . "/objectsync/collections/" . $this->id . "/revision");
			if (is_numeric($currentRevision))
			{
				$hasChanged = ($this->lastRevisionCheck < $currentRevision) ? true : false;
				$this->lastRevisionCheck = $currentRevision;
				return $hasChanged;
			}
            else
            {
                // Current revision has not been updated which means the collection has not been modified since last check
                return false;
            }
		}

		// Check if we have any stats to work with
		$result = $this->dbh->Query("select 1 as exists FROM object_sync_stats where collection_id='" . $this->id . "' limit 1");
		$hasChanged = ($this->dbh->GetNumberRows($result) > 0) ? true : false;

		// Set last checked revision for subsequent calls resulting in minimal db hits
		$this->lastRevisionCheck = $this->revision;

		return $hasChanged;
	}

	/**
	 * Increment the interval collection revision
	 */
	private function updateRevision()
	{
		// Increment
		$this->revision++;

		if (!$this->id)
			return false;

		// Save to persistant store
		$this->dbh->Query("UPDATE object_sync_partner_collections 
						   SET ts_last_sync='now', revision='" . $this->revision . "' 
						   WHERE id='".$this->id."'");
		
		// Save to cache for parallel processes
		$this->cache->set($this->dbh->accountId . "/objectsync/collections/" . $this->id . "/revision", $this->revision);
	}

	/**
	 * Check if this collection, or a subset of this collection by parentId, is initailized
	 *
	 * @param int $parentId Optional subset of collection
	 * @return bool true if collection or subject has been initialized
	 */
	public function isInitialized($parentId=null)
	{
		if (!$this->id)
			return false;

		if ($this->initialized)

		// If no parent or heirarch then just use collection init flag
		if (null == $parentId && false == $this->fInitialized)
			return false;

		if (null == $parentId && true == $this->fInitialized)
			return true;

		if (isset($this->initailizedParents[$parentId]))
			return $this->initailizedParents[$parentId];

		// Get from table
		$res = $this->dbh->Query("SELECT ts_completed FROM object_sync_partner_collection_init 
						   		  WHERE collection_id='".$this->id."' AND parent_id='" . (($parentId)?$parentId:'0') . "'");

		$isInit = ($this->dbh->GetNumberRows($res)>0) ? true : false;

		$this->initailizedParents[$parentId] = $isInit;

		return $isInit;
	}

	/**
	 * Set if this collection has been initialized or not
	 *
	 * @param int $parentId Optional subset of collection
	 */
	public function setIsInitialized($parentId=null, $isInit=true)
	{
		$this->fInitialized = $isInit;
		$this->initailizedParents[$parentId] = $isInit;

		if (!$this->id)
			return false;

        // Set initialized to true first because the below process may take some time
        // and we don't want multiple instances of this collection initailizing at once
        // due to multiple apache threads running
        $this->dbh->Query("UPDATE object_sync_partner_collections SET f_initialized='t' WHERE id='".$this->id."'");

		$res = $this->dbh->Query("SELECT ts_completed FROM object_sync_partner_collection_init 
						   		  WHERE collection_id='".$this->id."' AND parent_id='" . (($parentId) ? $parentId : '0') . "'");
		if ($this->dbh->GetNumberRows($res)==0)
		{
			$this->dbh->Query("INSERT INTO object_sync_partner_collection_init(collection_id, parent_id, ts_completed) 
								VALUES('".$this->id."', '".(($parentId) ? $parentId : '0')."', 'now');");
		}
	}

}
