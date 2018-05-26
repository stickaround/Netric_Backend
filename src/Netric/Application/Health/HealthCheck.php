<?php
namespace Netric\Application\Health;

use Netric\Db\Relational\RelationalDbInterface;
use Netric\FileSystem\FileStore\FileStoreInterface;
use Netric\Log\LogInterface;

/**
 * Create a new Application DataMapper service
 */
class HealthCheck implements HealthCheckInterface
{
    /**
     * Handle to system database to check
     *
     * @var RelationalDbInterface
     */
    private $database = null;

    /**
     * Handle to a remote file store to test
     *
     * @var FileStoreInterface
     */
    private $fileStore = null;

    /**
     * Main application log
     *
     * @var LogInterface|null
     */
    private $applicationLog = null;

    /**
     * Keep track of any failures so we can report to the caller
     *
     * @var string[]
     */
    private $reportedErrors = [];

    /**
     * The maximum number of errors between healtchecks before we
     * consider this application unhealthy.
     *
     * @var int
     */
    private $maxErrorsPerInterval = 3;

    /**
     * HealthCheck constructor.
     *
     * @param RelationalDbInterface $database System database to check connection to
     * @param FileStoreInterface $fileStore File store to check connection to
     */
    public function __construct(LogInterface $applicationLog, RelationalDbInterface $database, FileStoreInterface $fileStore)
    {
        $this->applicationLog = $applicationLog;
        $this->fileStore = $fileStore;
        $this->database = $database;
    }

    /**
     * Test if the application is healthy
     *
     * This typically will mean we have not thrown any unhanded
     * exceptions or logged any fatal/critical errors.
     *
     * @return bool true if healthy
     */
    public function isApplicationHealthy()
    {
        // Get statistics about log entries since last healthcheck
        $stats = $this->applicationLog->getLevelStats();

        // Reset the statistics for the next check
        $this->applicationLog->resetLevelStats();

        // Make sure we have not encountered enough errors to be unhealthy
        if (isset($stats['error']) && $stats['error'] >= $this->maxErrorsPerInterval) {
            return false;
        }

        // Any critical logs means we are unhealthy
        if (isset($stats['critical']) && $stats['critical'] >= 1) {
            return false;
        }

        return true;
    }

    /**
     * Conduct a systems test to to verify that the application
     * is healthy and that it can connect to all required dependencies
     *
     * @return bool true if healthy
     */
    public function isSystemHealthy()
    {
        // First check to see if the application is unhealthy
        if (!$this->isApplicationHealthy()) {
            return false;
        }

        // Check if we can connect and start transactions on the database
        if (!$this->database->isReady()) {
            $this->reportedErrors[] = "The database " . get_class($this->database) .
                " is not yet ready or cannot connect";
            return false;
        }

        // Check if we can connect to the file store
        if (!$this->fileStore->isReady()) {
            $this->reportedErrors[] = "The file store " . get_class($this->fileStore) .
                " is not yet ready or cannot connect";
            return false;
        }


        return true;
    }

    /**
     * Get array of all errors reported during the health check
     *
     * @return string[]
     */
    public function getReportedErrors()
    {
        return $this->reportedErrors;
    }
}
