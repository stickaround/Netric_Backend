<?php
namespace Netric\Application\Setup;

use Netric\Account\Account;
use Netric\Error\AbstractHasErrors;
use Netric\Console\BinScript;
use Netric\Log\LogInterface;
use Netric\Settings\SettingsFactory;

/**
 * Run updates on an account
 */
class AccountUpdater extends AbstractHasErrors
{
    /**
     * Account we are updating
     *
     * @var Account
     */
    private $account = null;

    /**
     * The current major, minor, and points versions for an account
     *
     * @var \stdClass
     */
    private $version = null;

    /**
     * Ticker used to track last updated to
     *
     * @var \stdClass
     */
    private $updatedToVersion = null;

    /**
     * The name of the name/value table that will hold the schema version
     *
     * @var string
     */
    public $tableName = "settings";

    /**
     * Determine whether to execute the updates or just do a dry-run
     *
     * Set boolean value false if we need just to get the system schema file version
     *
     * @var bool
     */
    private $executeUpdates = true;

    /**
     * Root path where the update scripts can be found
     *
     * @var string
     */
    private $rootPath = "";

    /**
     * Application log
     *
     * @var LogInterface
     */
    private $log = null;

    /**
     * Constructor
     *
     * @param Account $account
     */
    public function __construct(Account $account)
    {
        $this->account = $account;
        $this->log = $account->getApplication()->getLog();
        $this->version = new \stdClass();
        $this->updatedToVersion = new \stdClass();

        // Set default path
        $this->rootPath = dirname(__FILE__) . "/../../../../bin/scripts/update";

        // Get the current version from settings
        $this->getCurrentVersion();
    }

    /**
     * Save the last updated schema version to the settings for this account
     *
     * @returns The version that was saved
     */
    public function saveUpdatedVersion(): string
    {
        $updated = false;

        if ($this->updatedToVersion->major > $this->version->major) {
            $updated = true;
        } elseif ($this->updatedToVersion->major == $this->version->major && $this->updatedToVersion->minor > $this->version->minor) {
            $updated = true;
        } elseif ($this->updatedToVersion->major == $this->version->major && $this->updatedToVersion->minor == $this->version->minor
            && $this->updatedToVersion->point > $this->version->point) {
            $updated = true;
        }

        if ($updated) {
            $newversion = $this->updatedToVersion->major . "." . $this->updatedToVersion->minor . "." . $this->updatedToVersion->point;
            $settings = $this->account->getServiceManager()->get(SettingsFactory::class);
            $settings->set("system/schema_version", $newversion);
            return $newversion;
        }

        // Return current version since nothing was updated
        return implode('.', [$this->version->major, $this->version->minor, $this->version->point]);
    }

    /**
     * Set the current account to whatever the latest version is
     * so that new accounts do not need to re-run all the updates.
     * That should not hurt anything, but it is a waste of time.
     */
    public function setCurrentAccountToLatestVersion()
    {
        $latestversion = $this->getLatestVersion();
        $settings = $this->account->getServiceManager()->get(SettingsFactory::class);
        $settings->set("system/schema_version", $latestversion);

        // Refresh the current version state
        $this->getCurrentVersion();
    }

    /**
     * Gets the latest version of database schema from the file structure
     *
     * @return bool false on failure, true on success
     */
    public function getLatestVersion()
    {
        // Flag to make this a dry run with no actual updates performed
        $this->executeUpdates = false;

        // Temporarily set current version to 0 to force all updates to mock process
        // so we can increment updatedToVersion to the last possible update
        $originalCurrentVersion = clone $this->version;
        $this->version->major = 0;
        $this->version->minor = 0;
        $this->version->point = 0;

        // This will get the major, minor and point versions
        $this->runOnceUpdates();

        $versionParts[] = $this->updatedToVersion->major;
        $versionParts[] = $this->updatedToVersion->minor;
        $versionParts[] = $this->updatedToVersion->point;
        $latestVersion = implode(".", $versionParts);

        // Reset the flag
        $this->executeUpdates = true;

        // Rest updatedTo back to zero
        $this->updatedToVersion->major = 0;
        $this->updatedToVersion->minor = 0;
        $this->updatedToVersion->point = 0;

        // And reset the current version back to the originial
        $this->version = $originalCurrentVersion;

        return $latestVersion;
    }

    /**
     * Get the current setup version of this account
     *
     * @return string Version string
     */
    public function getCurrentVersion(): string
    {
        // Get the current version from settings
        $settings = $this->account->getServiceManager()->get(SettingsFactory::class);

        // We get bypassing any cache in case the version was reset in the db directly
        $version = $settings->getNoCache("system/schema_version");

        // Set current version counter
        $parts = explode(".", $version);
        $this->version->major = (isset($parts[0])) ? intval($parts[0]) : 1;
        $this->version->minor = (isset($parts[1])) ? intval($parts[1]) : 0;
        $this->version->point = (isset($parts[2])) ? intval($parts[2]) : 0;

        return implode('.', [$this->version->major, $this->version->minor, $this->version->point]);
    }

    /**
     * Update the root path to get update scripts from
     *
     * @param string $rootPath
     */
    public function setScriptsRootPath($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    /**
     * Run all updates for an account
     *
     * @return string Version in xxx.xxx.xxx format
     */
    public function runUpdates()
    {
        // Run the one time scripts first
        $version = $this->runOnceUpdates();

        // Now run scripts that are set to run on every update
        $this->runAlwaysUpdates();

        return $version;
    }

    /**
     * Run all scripts in the 'always' directory
     *
     * These run every time an update is executed
     *
     * @return bool true on sucess, false on failure
     */
    public function runAlwaysUpdates()
    {
        $updatePath = $this->rootPath . "/always";

        // Get individual update scripts
        $updates = array();
        $dir = opendir($updatePath);
        if ($dir) {
            while ($file = readdir($dir)) {
                if (!is_dir($updatePath . "/" . $file) && $file != '.' && $file != '..') {
                    $updates[] = $file;
                }
            }
            sort($updates);
            closedir($dir);
        }

        // Now process each of the update scripts
        $allStart = microtime(true);
        foreach ($updates as $update) {
            // It's possible to run through this without executing the scripts
            if (substr($update, -3) == "php" && $this->executeUpdates) {
                $this->log->info(
                    "AccountUpdater->runAlwaysUpdates: Running $updatePath.\"/\".$update" .
                        " for " . $this->account->getName()
                );
                // Execute a script only on the current account
                $script = new BinScript($this->account->getApplication(), $this->account);
                $script->run($updatePath . "/" . $update);
            }
        }
    }

    /**
     * Run versioned update scripts that only run once then increment version
     *
     * @return string Last processed version in xxx.xxx.xxx format
     */
    public function runOnceUpdates()
    {
        $updatePath = $this->rootPath . "/once";

        // Get major version directories
        $majors = array();
        $dir = opendir($updatePath);
        if ($dir) {
            while ($file = readdir($dir)) {
                if (is_dir($updatePath . "/" . $file) && $file[0] != '.') {
                    if ($this->version->major <= (int)$file) {
                        $majors[] = $file;
                    }
                }
            }
            sort($majors);
            closedir($dir);
        }

        // Get minor version directories
        foreach ($majors as $dir) {
            $this->processMinorDirs($dir, $updatePath);
        }

        // Save the last updated version
        $savedVersion = $this->saveUpdatedVersion();

        return $savedVersion;
    }

    /**
     * Process minor subdirectories in the major dir
     *
     * @param string $major The name of a major directory
     * @param string $base  The base or root of the path where the major dir is located
     * @return bool true on sucess, false on failure
     */
    private function processMinorDirs($major, $base)
    {
        $path = $base . "/" . $major;

        // Get major version directories
        $minors = array();
        $dir_handle = opendir($path);
        if ($dir_handle) {
            while ($file = readdir($dir_handle)) {
                if (is_dir($path . "/" . $file) && $file[0] != '.') {
                    if (($this->version->major == (int)$major
                        && $this->version->minor <= (int)$file)
                        || ($this->version->major < (int)$major)) {
                        $minors[] = $file;
                    }
                }
            }
            sort($minors);
            closedir($dir_handle);
        }

        // Pull updates/points from minor dirs
        foreach ($minors as $minor) {
            $ret = $this->processPoints($minor, $major, $base);
            if (!$ret) { // there was an error so stop processing
                return false;
            }
        }

        return true;
    }

    /**
     * Process minor subdirectories in the major dir
     *
     * @param string $minor The minor id we are working in now
     * @param string $major The major id we are working in now
     * @param string $base The base or root of the path where the major dir is located
     */
    private function processPoints($minor, $major, $base)
    {
        $path = $base . "/" . $major . "/" . $minor;

        // Get individual update points
        $updates = array();
        $points = array();
        $pointsVersion = array();
        $dir_handle = opendir($path);
        if ($dir_handle) {
            while ($file = readdir($dir_handle)) {
                if (!is_dir($path . "/" . $file) && $file != '.' && $file != '..') {
                    $point = substr($file, 0, -4); // remove .php to get point number

                    if (($this->version->major < (int)$major)
                        || ($this->version->major == (int)$major
                        && $this->version->minor < (int)$minor)
                        || ($this->version->major == (int)$major
                        && $this->version->minor == (int)$minor
                        && $this->version->point < (int)$point)) {
                        $points[] = (int)$point;
                        $updates[] = $file;
                    }
                    $pointsVersion[] = (int)$point;
                }
            }

            // Sort updates by points
            array_multisort($points, $updates);

            // Sort Points
            sort($pointsVersion);
            $pointsVersion = array_reverse($pointsVersion);

            closedir($dir_handle);
        }

        // Set the latest updated to variables
        $this->updatedToVersion->major = (int)$major;
        $this->updatedToVersion->minor = (int)$minor;

        // Pull updates/points from minor dirs
        foreach ($updates as $update) {
            // Make sure it is a php script
            if (substr($update, -3) == 'php') {
                // It's possible to run through this without executing the scripts
                if ($this->executeUpdates) {
                    $this->log->info(
                        "AccountUpdater->runOnceUpdates->processMinorDirs->processPoints: " .
                            "Running $path.\"/\".$update " .
                            "for " . $this->account->getName()
                    );

                    // Execute a script only on the current account
                    $script = new BinScript($this->account->getApplication(), $this->account);
                    $script->run($path . "/" . $update);

                    // Save the last updated version
                    $this->saveUpdatedVersion();
                }

                // Update the point
                $this->updatedToVersion->point = (int)substr($update, 0, -4);
            }
        }

        // If we didn't find any updates to run, then set to 0
        if (!isset($this->updatedToVersion->point)) {
            $this->updatedToVersion->point = 0;
        }
    }
}
