<?php

namespace Netric\Application\Health\DependencyCheck;

use JobQueueApiFactory\JobQueueApiFactory;

/**
 * Make sure we can connect to the jobqueue service
 */
class JobQueueDependencyCheck implements DependencyCheckInterface
{
    /**
     * Server or host of jobqueue
     */
    private string $server = "";

    /**
     * Constructor
     *
     * @param string $server SErver or host name
     */
    public function __construct(string $server)
    {
        $this->server = $server;
    }

    /**
     * Check if pgsql is running and available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        $clientFactory = new JobQueueApiFactory();
        $apiClient = $clientFactory->createJobQueueClient(gethostbyname($this->server));
        try {
            if ($apiClient->ping()) {
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
