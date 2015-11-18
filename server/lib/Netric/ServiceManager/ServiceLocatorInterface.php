<?php
/*
 * Define interface for a service locator
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\ServiceManager;

/**
 * Service factories are classes that handle the construction of complex/cumbersome services
 */
interface ServiceLocatorInterface
{
    /**
     * Get a service by name
     *
     * @param string $serviceName
     * @return mixed The service object and false on failure
     */
    public function get($serviceName);
}