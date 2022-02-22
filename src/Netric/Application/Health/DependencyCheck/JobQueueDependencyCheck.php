<?php

namespace Netric\Application\Health\DependencyCheck;

use JobQueueApi\JobClient;

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
     * Constructor
     *
     * @param JobClient $client
     */
    public function __construct(JobClient $jobClient)
    {
        $this->client = $jobClient;
    }

    /**
     * Check if pgsql is running and available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        try {
            if ($this->client->ping("hello") == "hello") {
                return true;
            }
        } catch (\Exception $exception) {
            return false;
        }

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
