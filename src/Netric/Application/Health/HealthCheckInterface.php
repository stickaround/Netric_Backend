<?php
namespace Netric\Application\Health;

/**
 * Methods for checking the health of the system
 */
interface HealthCheckInterface
{
    /**
     * Test if the application is healthy
     *
     * This typically will mean we have not thrown any unhanded
     * exceptions or logged any fatal/critical errors.
     *
     * @return bool true if healthy
     */
    public function isApplicationHealthy();

    /**
     * Conduct a systems test to to verify that the application
     * is healthy and that it can connect to all required dependencies
     *
     * @return bool true if healthy
     */
    public function isSystemHealthy();

    /**
     * Get array of all errors reported during the health check
     *
     * @return string[]
     */
    public function getReportedErrors();
}