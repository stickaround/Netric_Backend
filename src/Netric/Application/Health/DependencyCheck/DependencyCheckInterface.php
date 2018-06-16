<?php
namespace Netric\Application\Health\DependencyCheck;

/**
 * Common interface for all the external dependencies
 */
interface DependencyCheckInterface
{
    /**
     * Check if the dependency is available and ready for operation
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Get config values so that we can log it for troubleshooting
     *
     * @return string
     */
    public function getParamsDescription(): string;
}
