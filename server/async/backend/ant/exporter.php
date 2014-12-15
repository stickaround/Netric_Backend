<?php
/**
 * This is our exporter which requests the actual exporter from ICS and makes sure
 * that the ImportProxies are used.
 */
class ExportChangesAnt implements IExportChanges
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
	 * Ant Sync collection
	 *
	 * @var AntObjectSync_Collection
	 */
	public $collection = null;

	/**
	 * Imporer
	 *
	 * @var ImportChangesAnt
	 */
	public $importer = null;

	/**
	 * Sync state set in Config and returned from the stat engine
	 *
	 * @var array
	 */
	protected $syncstate;

	/**
	 * Array of changes to import
	 *
	 * @var int
	 */
	public $changes = array();

	/**
	 * The current step we are processing
	 *
	 * @var int
	 */
	private $step = 0;

	/**
	 * Param flags used to define behavior of sync
	 *
	 * @var int
	 */
	private $contentparameters = null;


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

	// Exporter interface functions
	// ============================================================================================
	
	/**
     * Initializes the exporter
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
		return true;
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
		if (!isset($this->syncstate) || !is_array($this->syncstate))
            throw new StatusException("DiffState->GetState(): Error, state not available", SYNC_FSSTATUS_CODEUNKNOWN, null, LOGLEVEL_WARN);

        return $this->syncstate;
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
		$this->contentparameters = $contentparameters;
        $this->collection->cutoffdate = Utils::GetCutOffDate($contentparameters->GetFilterType());

		return true;
	}

    /**
     * Sets the importer where the exporter will sent its changes to
     * This exporter should also be ready to accept calls after this
     *
     * @param object $importer Implementation of IImportChanges that sends data to the device
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function InitializeExporter(&$importer)
	{
		ZLog::Write(LOGLEVEL_DEBUG, "ExportChangesAnt::InitializeExporter Initializing");
		$this->changes = array();
        $this->step = 0;
        $this->importer = $importer;

		// Get the changes since the last sync
		if(!isset($this->syncstate) || !$this->syncstate)
			$this->syncstate = array();

		if($this->folderId) 
		{
            //do nothing if it is a dummy folder
			if ($this->folderId != SYNC_FOLDER_TYPE_DUMMY) 
			{
				// If this is an email folder, then get the grouping id and pass as first param
				$parentId = $this->getFolderGroupingId($this->folderId);
				$this->changes = $this->collection->getChangedObjects($parentId, false); // second param does not clear stat
            }

			ZLog::Write(LOGLEVEL_DEBUG, "ExportChangesAnt::InitializeExporter Initialized {$this->folderId} with " . count($this->changes) . " content changes");
        }
		else 
		{
            // Init other root folders for contacts and such if this is the first sync
			$addOtherRoots = ($this->collection->fInitialized) ? false : true;

			/*
			$this->changes = $this->collection->getChangedGroupings(false);

			// Conver id to full path
			$objDef = CAntObject::factory($this->backend->dbh, "email_message", null, $this->backend->user);
			for ($i = 0; $i < count($this->changes); $i++)
			{
				$this->changes[$i]["antid"] = $this->changes[$i]["id"]; // cache for later to make updating stats fast
				$this->changes[$i]["id"] = str_replace('/', '.', $objDef->getGroupingPathById("mailbox_id", $this->changes[$i]["id"]));
			}
			 */

			// Add other root folders after the fact
			if ($addOtherRoots)
			{
				$this->changes[] = array("id"=>"contacts_root", "action"=>"change");
				$this->changes[] = array("id"=>"calendar_root", "action"=>"change");
				$this->changes[] = array("id"=>"notes_root", "action"=>"change");
				$this->changes[] = array("id"=>"contacts_root", "action"=>"change");
				$this->changes[] = array("id"=>"Inbox", "action"=>"change");
			}
			
			ZLog::Write(LOGLEVEL_DEBUG, "ExportChangesAnt::InitializeExporter Got hierarchy with " . count($this->changes) . " changes");
        }

		return true;
	}

    /**
     * Returns the amount of changes to be exported
     *
     * @access public
     * @return int
     */
    public function GetChangeCount()
	{
		return count($this->changes);
	}

    /**
     * Synchronizes a change to the configured importer
     *
     * @access public
     * @return array        with status information
     */
	public function Synchronize()
	{
		$progress = array();

        // Get one of our stored changes and send it to the importer, store the new state if
        // it succeeds
		if($this->folderId == false) 
		{
			if($this->step < count($this->changes)) 
			{
                $change = $this->changes[$this->step];

				switch($change["action"]) 
				{
				case "change":
					$folder = $this->backend->GetFolder($change["id"]);

					if(!$folder)
						return;

					if($this->flags & BACKEND_DISCARD_DATA || $this->importer->ImportFolderChange($folder))
					{
						$this->collection->deleteStat($change['antid']);
						$this->updateState("change", $this->backend->StatFolder($change['id']));
					}
					break;

				case "delete":
					if($this->flags & BACKEND_DISCARD_DATA || $this->importer->ImportFolderDeletion($change["id"]))
					{
						$this->collection->deleteStat($change['antid']);
						$this->updateState("delete", $change);
					}
					break;
                }

                $this->step++;

                $progress = array();
                $progress["steps"] = count($this->changes);
                $progress["progress"] = $this->step;

                return $progress;
			} 
			else 
			{
                return false;
            }
        }
		else 
		{
			if($this->step < count($this->changes)) 
			{
                $change = $this->changes[$this->step];
				$parentId = $this->getFolderGroupingId($this->folderId); // Get folder id if set

				switch($change["action"])
				{
                    case "change":
                        // Note: because 'parseMessage' and 'statMessage' are two seperate
                        // calls, we have a chance that the message has changed between both
                        // calls. This may cause our algorithm to 'double see' changes.
                        $message = $this->backend->Fetch($this->folderId, $change["id"], $this->contentparameters);

                        // copy the flag to the message
                        $message->flags = (isset($change["flags"])) ? $change["flags"] : 0;

						if($message) 
						{
                            if($this->flags & BACKEND_DISCARD_DATA || $this->importer->ImportMessageChange($change["id"], $message) == true)
							{
								$this->collection->deleteStat($change['id'], $parentId);
								$this->updateState("change", $this->backend->StatMessage($this->folderId, $change["id"]));
							}
                        }
                        break;

                    case "delete":
                        if($this->flags & BACKEND_DISCARD_DATA || $this->importer->ImportMessageDeletion($change["id"]) == true)
						{
							$this->collection->deleteStat($change['id'], $parentId);
							$this->updateState("delete", $change);
						}
                        break;

					/* The below are not used in Netric AntSync yet
                    case "flags":
                        if($this->flags & BACKEND_DISCARD_DATA || $this->importer->ImportMessageReadFlag($change["id"], $change["flags"]) == true)
							$this->collection->deleteStat($change['id'], $parentId);
                        break;

                    case "move":
                        if($this->flags & BACKEND_DISCARD_DATA || $this->importer->ImportMessageMove($change["id"], $change["parent"]) == true)
							$this->collection->deleteStat($change['id'], $parentId);
                        break; */
                }

                $this->step++;

                $progress = array();
                $progress["steps"] = count($this->changes);
                $progress["progress"] = $this->step;

                return $progress;
			} 
			else 
			{
                return false;
            }
        }
	}

	// Utility functions
	// =======================================================================

	/**
	 * Get the id of a folder if it is an email grouping
	 *
	 * @param string $folderId The folder id to get the id for
	 * @return int If it is an email mailbox, otherwise return null
	 */
	private function getFolderGroupingId($folderId)
	{
		$ret = null;

		switch ($folderId)
		{
		case 'contacts_root':
		case 'tasks_root':
		case 'calendar_root':
		case 'notes_root':
			// Return null
			break;
		default:
			if ($folderId)
			{
				$folderId = str_replace('.', '/', $folderId);
				$objDef = CAntObject::factory($this->backend->dbh, "email_message", null, $this->backend->user);
				$grp = $objDef->getGroupingEntryByPath("mailbox_id", $folderId);
				if ($grp['id'])
					$ret = $grp['id'];
			}
			break;
		}

		return $ret;
	}


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
