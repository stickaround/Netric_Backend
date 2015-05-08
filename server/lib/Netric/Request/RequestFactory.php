<?php
/**
 * Factory used to initialize the current request
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Request;

use Netric\ServiceManager;
use Netric\Console\Console;

/**
 * Create a request object
 *
 * @package Netric\Request
 */
class RequestFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return RequestInterface
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        if (Console::isConsole()) { 
            return new ConsoleRequest();
        }

        return new HttpRequest();
    }
}
