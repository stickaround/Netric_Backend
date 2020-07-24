<?php
/**
 * Netric exporter handles exporting folder changes from netric to the device
 *
 * The reason all the files are lowercase in here is because that is the z-push standard
 * so we stick with it to be consistent.
 */
$zPushRoot = dirname(__FILE__) ."/../../";

// Interfaces we are extending
require_once($zPushRoot . 'lib/interface/iexportchanges.php');

// Supporting files and exceptions
require_once($zPushRoot . 'lib/core/zpush.php');
require_once($zPushRoot . 'lib/request/request.php');
require_once($zPushRoot . 'lib/exceptions/authenticationrequiredexception.php');
require_once($zPushRoot . 'lib/exceptions/statusexception.php');

// Local backend files
require_once($zPushRoot . 'backend/netric/changesnetric.php');
require_once($zPushRoot . 'backend/netric/entityprovider.php');

// Include netric autoloader for all netric libraries
require_once(dirname(__FILE__) . "/../../../../init_autoloader.php");

/**
 * Handle exporting folder changes from netric to the device
 */
class ExportFolderChangeNetric extends ChangesNetric implements IExportChanges
{
    /**
     * The current step we are processing
     *
     * @var int
     */
    private $step = 0;

    /**
     * Imporer
     *
     * @var ImportChangesNetric
     */
    private $importer = null;

    /**
     * Netric log
     *
     * @var Netric\Log
     */
    private $log = null;

    /**
     * Array of changes to import
     *
     * @var array('id', 'type'=>'change'|'delete', 'flags', 'mod')
     */
    private $changes = [];

    /**
     * Constructor
     *
     * @param Netric\Log\LogInterface $log Logger for recording what is going on
     * @param EntityProvider $entityProvider Write and read entities from netric
     */
    public function __construct(
        Netric\Log\LogInterface $log,
        EntityProvider $entityProvider
    ) {
        $this->log = $log;
        $this->provider = $entityProvider;
    }

    /**
     * Sets the importer where the exporter will sent its changes to
     *
     * This exporter should also be ready to accept calls after this
     *
     * @param object        &$importer      Implementation of IImportChanges
     *
     * @access public
     * @return boolean
     * @throws StatusException
     */
    public function InitializeExporter(&$importer)
    {
        $this->log->debug("ZPUSH->InitializeExporter Initializing");
        $this->changes = [];
        $this->step = 0;
        $this->importer = $importer;

        // Get folder hierarchy
        $folders = $this->provider->getAllFolders();

        // Convert the folders to stats
        $hierarchy = [];
        foreach ($folders as $folder) {
            $hierarchy[] = [
                "id" => $folder->serverid,
                "flags" => 0,
                "mod" => $folder->displayname
            ];
        }

        // Get a diff of any changes made compared to the state from last sync
        $this->changes = $this->getDiffTo($hierarchy);

        $this->log->info("ZPUSH->ExportFolderChangeNetric:InitializeExporter Got hierarchy with " . count($this->changes) . " changes");

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
     * @throws StatusException if a sync action is not supported
     * @return array|bool Array with status data if success, false if there are no changes
     */
    public function Synchronize()
    {
        // Get one of our stored changes and send it to the importer, store the new state it succeeds
        if ($this->step < count($this->changes)) {
            $change = $this->changes[$this->step];

            switch ($change["type"]) {
                case "change":
                    $folder = $this->provider->getFolder($change["id"]);

                    // The folder was apparently deleted between the time we changed and now
                    if (!$folder) {
                        throw new StatusException("The folder {$change['id']} could not be opened");
                    }

                    if ($this->flags & BACKEND_DISCARD_DATA || $this->importer->ImportFolderChange($folder)) {
                        $this->updateState(
                            $change["type"],
                            [
                                "type" => $change['type'],
                                "parent" => $folder->parentid,
                                "id" => $change['id'],
                                "mod" => $folder->displayname
                            ]
                        );
                    }
                    break;

                case "delete":
                    $syncFolder = new \SyncFolder();
                    $syncFolder->serverid = $change['id'];
                    $syncFolder->displayname = "Delete-" . $change['id'];
                    if ($this->flags & BACKEND_DISCARD_DATA || $this->importer->ImportFolderDeletion($syncFolder)) {
                        // Delete action only requires id in the stat data
                        $this->updateState(
                            $change["type"],
                            ["id" => $change['id']]
                        );
                    }
                    break;
                default:
                    // Not supported
                    throw new StatusException("Sync type {$change['type']} not supported");
            }


            $this->step++;

            return [
                "steps" => count($this->changes),
                "progress" => $this->step
            ];
        } else {
            return false;
        }
    }

    /**----------------------------------------------------------------------------------------------------------
     * DiffState specific stuff
     */

    /**
     * Differential mechanism compares the current syncState to the sent $new
     *
     * This is only used for folder hierarchy since we have to combine them
     * and no single netric sync collection will contain all the changes.
     *
     * @param array $new
     * @return array
     */
    private function getDiffTo($new)
    {
        $changes = [];

        // Convert array to map to make it easy to diff
        $newMap = [];
        foreach ($new as $newState) {
            $newMap[$newState['id']] = $newState;
        }
        $syncStateMap = [];
        foreach ($this->syncState as $state) {
            $syncStateMap[$state['id']] = $state;
        }

        // Get any new folders (groups in netric) in $new
        foreach ($newMap as $id => $newState) {
            if (!isset($syncStateMap[$id])) {
                // New folder found in $newState
                $changes[] = [
                    'type' => 'change',
                    'flags' => SYNC_NEWMESSAGE,
                    'id' => $id,
                ];
            }
            // TODO: Should we check for a name change in mod?
        }

        // Find any folders that have been deleted (in syncState but not in new)
        foreach ($syncStateMap as $id => $state) {
            if (!isset($newMap[$id])) {
                // New folder found in $newState
                $changes[] = [
                    'type' => 'delete',
                    'id' => $id,
                ];
            }
        }

        return $changes;
    }
}
