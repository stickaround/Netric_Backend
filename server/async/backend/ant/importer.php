<?php
/**
 * This is our local importer. Tt receives data from the PDA, for contents and hierarchy changes.
 * It must therefore receive the incoming data and convert it into ANT objects.
 * The creation of folders is fairly trivial, because folders that are created on
 * the PDA are always e-mail folders.
 */
class ImportChangesAnt implements IImportChanges 
{
	/**
	 * Folder id
	 * 
	 * @var string
	 */
	public $folderId = false;

	/**
	 * Backend
	 *
	 * @var IBackend
	 */
	public $backend = null;

	/**
	 * Sync state set in Config and returned from the stat engine
	 *
	 * @var array
	 */
	protected $syncstate;

	/**
	 * Config flags
	 *
	 * @var int
	 */
	protected $flags;

	/**
	 * Ant Sync collection
	 *
	 * @var AntObjectSync_Collection
	 */
	public $collection = null;

	/**
	 * Class constructor
	 *
	 * @param BackendAnt $backend Backend instance
	 */
	public function __construct($backend, $folderId=false)
	{
		$this->backend = $backend;
		$this->folderId = $folderId;
		$this->collection = $backend->getSyncCollection($folderId);
	}

	// Import interface functions
	// ==================================================================================
	
	/**
     * Initializes the importer
     *
     * @param string        $state
     * @param int           $flags
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
	public function Config($state, $flags = 0) 
	{
		if ($state == "")
            $state = array();

        if (!is_array($state))
            throw new StatusException("Invalid state", SYNC_FSSTATUS_CODEUNKNOWN);

        $this->syncstate = $state;
        $this->flags = $flags;
	}

	/**
     * Configures additional parameters used for content synchronization
     *
     * @param ContentParameters         $contentparameters
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
	public function ConfigContentParameters($contentparameters)
	{
		return false;
	}

	/**
     * Reads state from the Importer
     *
     * @access public
     * @return string
     * @throws StatusException
     */
	public function GetState()
	{
		return $this->syncstate;
	}

    /**
     * Loads objects which are expected to be exported with the state
     * Before importing/saving the actual message from the mobile, a conflict detection should be done
     *
     * @param ContentParameters         $contentparameters
     * @param string                    $state
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
	public function LoadConflicts($contentparameters, $state)
	{
		return true;
	}

    /**
     * Imports a single message
     *
     * @param string        $id
     * @param SyncObject    $message
     *
     * @access public
     * @return boolean/string               failure / id of message
     * @throws StatusException
     */
	public function ImportMessageChange($id, $message)
	{
		$oid = false;
		$obj = null;

		switch ($this->folderId)
		{
		case 'contacts_root':
			$obj = $this->backend->saveContact($id, $message);
			break;
		case 'tasks_root':
			$obj = $this->backend->saveTask($id, $message);
			break;
		case 'calendar_root':
			$obj = $this->backend->saveAppointment($id, $message);
			break;
		case 'notes_root':
			$obj = $this->backend->saveNote($id, $message);
			break;
		default:
			if ($this->folderId != false)
				$obj = $this->backend->saveEmailMessage($id, $message);
			break;
		}

		if ($obj)
		{
			// The first param should be the id of the remote object, but that is not passed so just use obj->id again
			$parentId = ($obj->def->parentField) ? $obj->getValue($obj->def->parentField) : null;
			$this->collection->updateImportObjectStat($obj->id, $obj->revision, $obj->id, $parentId);
			$this->updateState("change", array("id"=>$obj->id));
			$oid = $obj->id;
		}

		return $oid;
	}

    /**
     * Imports a deletion. This may conflict if the local object has been modified
     *
     * @param string        $id
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
	public function ImportMessageDeletion($id)
	{
		$ret = false;

		switch ($this->folderId)
		{
		case 'contacts_root':
			$ret = $this->backend->deleteContact($id);
			break;
		case 'tasks_root':
			$ret = $this->backend->deleteTask($id);
			break;
		case 'calendar_root':
			$ret = $this->backend->deleteAppointment($id);
			break;
		case 'notes_root':
			$ret = $this->backend->deleteNote($id);
			break;
		default:
			if ($this->folderId != false)
				$ret = $this->backend->deleteEmailMessage($id);
			break;
		}

		// Remove from imported stats link to local object id
		$this->collection->deleteImportObjectStat($id, $id); // The second param deletes outgoing stat in this collection
		$this->updateState("delete", array("id"=>$obj->id));

		return $ret;
	}

    /**
     * Imports a change in 'read' flag
     * This can never conflict
     *
     * @param string        $id
     * @param int           $flags
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
	public function ImportMessageReadFlag($id, $flags)
	{
		$ret = false;

		switch ($this->folderId)
		{
		case 'contacts_root':
		case 'tasks_root':
		case 'calendar_root':
		case 'notes_root':
			// Do nothing - not supported
			break;
		default:
			if ($this->folderId != false)
			{
				$obj = $this->backend->markEmailMessageRead($id);

				if ($obj->id)
				{
					$parentId = ($obj->def->parentField) ? $obj->getValue($obj->def->parentField) : null;
					$this->collection->updateImportObjectStat($obj->id, $obj->revision, $obj->id, $parentId);
					$this->updateState("change", array("id"=>$obj->id));
				}

				$ret = true;
			}
			break;
		}

		return $ret;
	}

    /**
     * Imports a move of a message. This occurs when a user moves an item to another folder
     *
     * @param string        $id
     * @param string        $newfolder      destination folder
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
	public function ImportMessageMove($id, $newfolder)
	{
		$ret = false;

		switch ($this->folderId)
		{
		case 'contacts_root':
		case 'tasks_root':
		case 'calendar_root':
		case 'notes_root':
			// Do nothing - not supported
			break;
		default:
			if ($this->folderId != false)
			{
				$obj = $this->backend->moveEmailMessage($id, $newfolder);

				if ($obj->id)
				{
					$parentId = ($obj->def->parentField) ? $obj->getValue($obj->def->parentField) : null;
					$this->collection->updateImportObjectStat($obj->id, $obj->revision, $obj->id, $parentId);
					$this->updateState("change", array("id"=>$obj->id));
				}

				$ret = true;
			}
			break;
		}

		return $ret;
	}	

	// Methods to import hierarchy
	// ==================================================================================

    /**
     * Imports a change on a folder
     *
     * @param object        $folder         SyncFolder
     *
     * @access public
     * @return boolean/string               status/id of the folder
     * @throws StatusException
     */
	public function ImportFolderChange($folder)
	{
		return false;
	}

    /**
     * Imports a folder deletion
     *
     * @param string        $id
     * @param string        $parent id
     *
     * @access public
     * @return boolean/int  success/SYNC_FOLDERHIERARCHY_STATUS
     * @throws StatusException
     */
	public function ImportFolderDeletion($id, $parent = false)
	{
		return false;
	}

	// Local helpers
	// ==================================================================================

	/**
     * Update the local state to reflect changes.
     *
     * @param string $type of change
     * @param array $change
     *
     * @access protected
     * @return
     */
	protected function updateState($type, $change) 
	{
        // Change can be a change or an add
		if($type == "change") 
		{
			for($i=0; $i < count($this->syncstate); $i++) 
			{
				if($this->syncstate[$i]["id"] == $change["id"]) 
				{
                    $this->syncstate[$i] = $change;
                    return;
                }
            }

            // Not found, add as new
            $this->syncstate[] = $change;
		} 
		else 
		{
			for($i=0; $i < count($this->syncstate); $i++) 
			{
                // Search for the entry for this item
				if($this->syncstate[$i]["id"] == $change["id"]) 
				{
					if($type == "flags")  
                        $this->syncstate[$i]["flags"] = $change["flags"]; // Update flags
                    else if($type == "delete")
                        array_splice($this->syncstate, $i, 1); // Delete item;
                    return;
                }
            }
        }
    }
}
