<?php
/**
 * StateMachine to store async states in a database rather than the default files
 */
$zPushRoot = dirname(__FILE__) ."/../../";

// Interfaces we are implementing
require_once($zPushRoot . 'lib/interface/istatemachine.php');

// Include netric autoloader for all netric libraries
require_once(dirname(__FILE__)."/../../../../init_autoloader.php");

use Netric\Db\DbInterface;
use Netric\Log\LogInterface;
use Netric\Cache\CacheInterface;
use Netric\Settings\Settings;

/**
 * IStateMachine using the netric database
 */
class NetricStateMachine implements IStateMachine
{
    /**
     * Netric log
     *
     * @var LogInterface
     */
    private $log = null;

    /**
     * Active database connection
     *
     * @var DbInterface
     */
    private $db = null;

    /**
     * Account settings service
     *
     * @var Settings
     */
    private $settings = null;

    /**
     * Version constants
     */
    const SUPPORTED_STATE_VERSION = IStateMachine::STATEVERSION_02;
    const VERSION = "version";

    /**
     * Constructor
     *
     * @param LogInterface $log Logger for recording what is going on
     * @param DbInterface $db Handle to database for account
     * @param CacheInterface $cache Store what we can in cache to speed things up
     * @param Settings $settings Account settings service
     */
    public function __construct(
        LogInterface $log,
        DbInterface $db = null,
        CacheInterface $cache = null,
        Settings $settings = null
    ) {
        $this->log = $log;
        $this->db = $db;
        $this->settings = $settings;
    }

    /**
     * Set the database to be used
     *
     * This is typically used when a user logs in to make sure we are writing to
     * the correct account database.
     *
     * @param DbInterface $db
     */
    public function setDatabase(DbInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Set the settings service to be used for an account
     *
     * This is typically used when a user logs in to make sure we are writing to
     * the correct account database.
     *
     * @param Settings $settings
     */
    public function setSettingsService(Settings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Gets a hash value indicating the latest dataset of the named
     * state with a specified key and counter.
     *
     * If the state is changed between two calls of this method
     * the returned hash should be different
     *
     * @param string $devid The device id
     * @param string $type The state type
     * @param string|bool $key (opt)
     * @param string|bool $counter (opt)
     *
     * @return string
     * @throws StateNotFoundException, StateInvalidException
     */
    public function GetStateHash($devid, $type, $key = false, $counter = false)
    {
        $db = $this->getDatabase();
        $hash = null;
        $record = null;

        $sql = "SELECT updated_at FROM async_device_states WHERE " .
           "device_id='" . $db->escape($devid) . "' AND " .
           "state_type='" . $db->escape($type) . "' AND " .
           "uuid ". (($key) ? "='$key'" : 'IS NULL') . " AND " .
           "counter=" . $db->escapeNumber((int)$counter);
        $results = $db->query($sql);
        if (!$db->getNumRows($results)) {
            throw new StateNotFoundException(
                "SqlStateMachine->GetStateHash(): Could not locate state with error:" .
                $db->getLastError()
            );
        } else {
            // datetime->format("U") returns EPOCH
            $row = $db->getRow($results);
            $datetime = new DateTime($row["updated_at"]);
            $hash = $datetime->format("U");
        }

        return $hash;
    }

    /**
     * Gets a state for a specified key and counter
     *
     * This method should call IStateMachine->CleanStates()
     * to remove older states (same key, previous counters).
     *
     * @param string $devid The device id
     * @param string $type The state type
     * @param string|bool $key Unique key to look for
     * @param bool|int $counter Optional counter
     * @param bool $cleanstates Optional if true then clean stale states
     *
     * @return mixed
     * @throws StateNotFoundException, StateInvalidException, UnavailableException
     */
    public function GetState($devid, $type, $key = false, $counter = false, $cleanstates = true)
    {
        $db = $this->getDatabase();

        // Convert boolean to null since the interface uses false to represent null
        if ($key === false) {
            $key = null;
        }

        $this->log->debug(
            "NetricStateMachine->GetState(): devid: $devid type: $type key: $key " .
            "counter: " . var_export($counter, true)
        );

        // If we have incremented past 0 and indicated we want to clean stale states then do it now
        if ($counter && $cleanstates) {
            $this->CleanStates($devid, $type, $key, $counter);
        }

        $sql = "SELECT state_data FROM async_device_states WHERE " .
            "device_id='" . $this->db->escape($devid) . "' AND " .
            "state_type='" . $this->db->escape($type) . "' AND " .
            "uuid ". (($key) ? "='$key'" : 'IS NULL') . " AND " .
            "counter=" . $this->db->escapeNumber((int)$counter);
        $data = null;
        $results = $db->query($sql);
        if (!$db->getNumRows($results)) {
            // throw an exception on all other states, but not FAILSAVE
            // as it's most of the times not there by default
            if ($type !== IStateMachine::FAILSAVE) {
                throw new StateNotFoundException("Could not locate state");
            }
        } else {
            $row = $db->getRow($results);
            if (is_string($db->unEscapeBytea($row["state_data"]))) {
                // DB returns a string for LOB objects
                $data = unserialize($db->unEscapeBytea($row["state_data"]));
            } else {
                $data = unserialize(stream_get_contents($row["state_data"]));
            }
        }

        return $data;
    }

    /**
     * Writes ta state to for a key and counter
     *
     * @param mixed $state
     * @param string $devid The device id
     * @param string $type The state type
     * @param string|bool $key (opt)
     * @param int|bool $counter (opt)
     * @return boolean
     * @throws UnavailableException
     */
    public function SetState($state, $devid, $type, $key = false, $counter = false)
    {
        $db = $this->getDatabase();

        // Convert boolean to null since the interface uses false to represent null
        if ($key === false) {
            $key = null;
        }

        // Set default counter
        if ($counter === false) {
            $counter = 0;
        }

        $this->log->debug("NetricStateMachine->SetState(): devid: $devid type: $type key: $key " .
            "counter: " . var_export($counter, true) . "");

        $sql = "SELECT device_id FROM async_device_states WHERE " .
            "device_id='" . $db->escape($devid) . "' AND " .
            "state_type='" . $db->escape($type) . "' AND " .
            "uuid ". (($key) ? "='$key'" : 'IS NULL') . " AND " .
            "counter=" . $db->escapeNumber((int)$counter);
        $results = $db->query($sql);

        // Either insert or update the state
        if (!$db->getNumRows($results)) {
            // New record
            $sql = "INSERT INTO async_device_states ".
                   "(device_id, state_type, uuid, counter, state_data, created_at, updated_at) " .
                   "VALUES (
                    '" . $db->escape($devid) . "', 
                    '" . $db->escape($type) . "', 
                    " . ((!$key) ? 'NULL' : "'" . $db->escape($key) . "'") . ", 
                    " . $db->escapeNumber($counter) . ", 
                    '" . $db->escapeBytea(serialize($state)) . "', 
                    'now', 
                    'now'
                   )";
        } else {
            // Existing record, we update it
            $sql = "UPDATE async_device_states SET " .
                        "state_data = '" . $db->escapeBytea(serialize($state)) . "', " .
                        "updated_at = 'now' " .
                    "WHERE " .
                        "device_id='" . $db->escape($devid) . "' AND " .
                        "state_type='" . $db->escape($type) . "' AND " .
                        "uuid ". (($key) ? "='$key'" : 'IS NULL') . " AND " .
                        "counter=" . $db->escapeNumber((int)$counter);
        }

        // Run query and check for error
        if (!$db->query($sql)) {
            throw new UnavailableException(
                "NetricStateMachine->SetState(): Could not write state:" . $db->getLastError()
            );
        }

        return strlen(serialize($state));
    }

    /**
     * Cleans up all older states
     *
     * If called with a $counter, all states previous state counter can be removed
     * If called without $counter, all keys (independently from the counter) can be removed
     *
     * @param string $devid The device id
     * @param string $type The state type
     * @param string $key They unique key to delete
     * @param string|bool $counter Sll states previous state counter can be removed
     * @param bool $thisCounterOnly Only clean this counter
     */
    public function CleanStates($devid, $type, $key, $counter = false, $thisCounterOnly = false)
    {
        $db = $this->getDatabase();

        // Convert boolean to null since the interface uses false to represent null
        if ($key === false) {
            $key = null;
        }

        $this->log->debug(
            "NetricStateMachine->CleanStates(): devid: $devid type: $type key: $key " .
            "counter: " . var_export($counter, true) . " " .
            "thisCounterOnly: " . var_export($thisCounterOnly, true)
        );

        $sql = "DELETE FROM async_device_states WHERE " .
            "device_id='" . $db->escape($devid) . "' AND " .
            "state_type='" . $db->escape($type) . "' AND " .
            "uuid ". (($key) ? "='$key'" : 'IS NULL') . " AND ";
        if ($counter === false) {
            // Remove all the states. Counter are 0 or >0, then deleting >= 0 deletes all
            $sql .= "counter >= 0";
        } else if ($counter !== false && $thisCounterOnly === true) {
            $sql .= "counter = " . $db->escapeNumber((int)$counter);
        } else {
            $sql .= "counter < " . $db->escapeNumber((int)$counter);
        }

        // Execute and check for error
        if (!$db->query($sql)) {
            $this->log->error(
                "NetricStateMachine->CleanStates(): Error cleaning states " .
                $db->getLastError()
            );
        }
    }

    /**
     * Links a user to a device
     *
     * @param string $username The user to link the device to
     * @param string $devid The device id to link
     * @return boolean indicating if the user was added or not (existed already)
     */
    public function LinkUserDevice($username, $devid)
    {
        $db = $this->getDatabase();
        $this->log->debug("NetricStateMachine->LinkUserDevice(): devid: $devid username: $username");

        $sql = "SELECT username FROM async_users WHERE " .
                "username = '" . $db->escape($username) . "' AND " .
                "device_id = '" . $db->escape($devid) . "'";
        $results = $db->query($sql);
        if ($db->getNumRows($results)) {
            // User is already linked
            $this->log->debug("NetricStateMachine->LinkUserDevice(): already linked so nothing changed");
            return false;
        } else {
            $sql = "INSERT INTO async_users (username, device_id) " .
                   "VALUES ('" . $db->escape($username) . "', '" . $db->escape($devid) . "')";
            if ($db->query($sql)) {
                $this->log->debug("NetricStateMachine->LinkUserDevice(): Linked device $devid to $username");
                return true;
            } else {
                $this->log->error("NetricStateMachine->LinkUserDevice(): Unable to link device $devid to $username");
                return false;
            }
        }
    }

    /**
     * Unlinks a device from a user
     *
     * @param string $username The username to link with a device
     * @param string $devid The device to link to the user
     * @return bool true if unlinked and false if nothing was unlinked
     */
    public function UnLinkUserDevice($username, $devid)
    {
        $db = $this->getDatabase();
        $this->log->debug("NetricStateMachine->UnLinkUserDevice(): devid: $devid username: $username");

        // First check to see if the user exists
        $sql = "SELECT username FROM async_users WHERE " .
            "username = '" . $db->escape($username) . "' AND " .
            "device_id = '" . $db->escape($devid) . "'";
        $results = $db->query($sql);
        if ($db->getNumRows($results)) {
            // We found a link
            $sql = "DELETE FROM async_users WHERE " .
                "username = '" . $db->escape($username) . "' AND " .
                "device_id = '" . $db->escape($devid) . "'";
            if ($db->query($sql)) {
                $this->log->debug(
                    "NetricStateMachine->LinkUserDevice(): user-device unlinked $devid:$username"
                );
                return true;
            } else {
                $this->log->error(
                    "NetricStateMachine->LinkUserDevice(): error unlinking $devid:$username: " .
                    $db->getLastError()
                );
                return false;
            }
        } else {
            // User device link does not exist
            $this->log->debug("NetricStateMachine->UnLinkUserDevice(): nothing to unlink");
            return false;
        }
    }

    /**
     * Returns an array with all device ids for a user
     *
     * If no user is set, all device ids should be returned
     *
     * @param string|bool $username Optional username if set to only get devices for
     * @return array
     */
    public function GetAllDevices($username = false)
    {
        $db = $this->getDatabase();
        $this->log->debug("NetricStateMachine->GetAllDevices(): username: " . var_export($username, true));

        if ($username === false) {
            // We also need to find potentially obsolete states that have no link to the async_users table anymore
            $sql = "SELECT DISTINCT(device_id) FROM async_device_states ORDER BY device_id";
        } else {
            $sql = "SELECT device_id FROM async_users WHERE " .
                   "username = '" . $db->escape($username) . "' ORDER BY device_id";
        }
        $results = $db->query($sql);

        if (!$results) {
            $this->log->error(
                "NetricStateMachine->GetAllDevices(): could not get devices: " .
                $db->getLastError()
            );
            return [];
        }

        // Get all devices
        $out = [];
        $num = $db->getNumRows($results);
        for ($i = 0; $i < $num; $i++) {
            $row = $db->getRow($results, $i);
            $out[] = $row['device_id'];
        }
        return $out;
    }

    /**
     * Returns the current version of the state files
     *
     * @return int
     */
    public function GetStateVersion()
    {
        $settings = $this->getSettings();
        $version = $settings->get("async/version");

        // If we have not saved the version before then do it now
        if (!$version) {
            $version = self::SUPPORTED_STATE_VERSION;
            $this->SetStateVersion($version);
        }

        $this->log->debug("NetricStateMachine->GetStateVersion(): supporting version '$version'");

        return $version;
    }

    /**
     * Sets the current version of the state files
     *
     * @param int $version the new supported version
     *
     * @return boolean
     */
    public function SetStateVersion($version)
    {
        $settings = $this->getSettings();
        $this->log->debug("NetricStateMachine->SetStateVersion(): version '$version'");
        return $settings->set("async/version", $version);
    }

    /**
     * Returns all available states for a device id
     *
     * @param string $devid The device id
     *
     * @return array(mixed)
     */
    public function GetAllStatesForDevice($devid)
    {
        $db = $this->getDatabase();
        $this->log->debug("NetricStateMachine->GetAllStatesForDevice(): devid '$devid'");

        $sql = "SELECT state_type, uuid, counter FROM async_device_states WHERE " .
               "device_id = '" . $db->escape($devid) . "' ORDER BY id_state";
        $result = $db->query($sql);

        // Log any errors
        if (!$result) {
            $this->log->error(
                "NetricStateMachine->GetAllStatesForDevice(): Failed to get states - " .
                $db->getLastError()
            );
            return [];
        }

        // Send all states minus state_data since that would be way too big
        $out = [];
        $num = $db->getNumRows($result);
        for ($i = 0; $i < $num; $i++) {
            $row = $db->getRow($result, $i);
            $state = array('type' => false, 'counter' => false, 'uuid' => false);
            if ($row["state_type"] !== null && strlen($row["state_type"]) > 0) {
                $state["type"] = $row["state_type"];
            } else if ($row["counter"] !== null && is_numeric($row["counter"])) {
                $state["type"] = "";
            }
            if ($row["counter"] !== null && strlen($row["counter"]) > 0) {
                $state["counter"] = $row["counter"];
            }
            if ($row["uuid"] !== null && strlen($row["uuid"]) > 0) {
                $state["uuid"] = $row["uuid"];
            }
            $out[] = $state;
        }

        return $out;
    }

    /**
     * Get the account database
     *
     * @return DbInterface
     * @throws RuntimeException If the settings services has not been set
     */
    private function getDatabase()
    {
        if (!$this->db) {
            throw new RuntimeException("The account database has not been set yet");
        }
        return $this->db;
    }

    /**
     * Get settings service
     *
     * @return Settings
     * @throws RuntimeException If the settings services has not been set
     */
    private function getSettings()
    {
        if (!$this->settings) {
            throw new RuntimeException("The settings service has not been set yet");
        }

        return $this->settings;
    }
}
