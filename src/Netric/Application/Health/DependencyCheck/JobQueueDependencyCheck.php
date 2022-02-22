<?php

namespace Netric\Application\Health\DependencyCheck;

use JobQueueApi\JobClient;
use Netric\Log\LogInterface;

/**
 * Make sure we can connect to the jobqueue service
 */
class JobQueueDependencyCheck implements DependencyCheckInterface
{
    /**
     * The jobqueue api client
     */
    private JobClient $client;

    /**
     * Logger used to debug depdency problems
     */
    private LogInterface $log;

    /**
     * Constructor
     *
     * @param JobClient $client
     */
    public function __construct(JobClient $jobClient, LogInterface $log)
    {
        $this->client = $jobClient;
        $this->log = $log;
    }

    /**
     * Check if pgsql is running and available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            if ($this->client->ping()) {
                return true;
            }
        } catch (\Exception $exception) {
            $this->log->warning("JobQueueDependencyCheck: Connection failed: " . $exception->getMessage());
            return false;
        }

        $this->log->warning("JobQueueDependencyCheck: Unable to ping");
        return false;
    }

    /**
     * Get config values so that we can log it for troubleshooting
     *
     * @return string
     */
    public function getParamsDescription(): string
    {
        return "NA";
    }
}
