<?php
/**
 * Factory used to initialize the netric filesystem
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Authentication;

use Netric\ServiceManager;

/**
 * Create an authentication service
 *
 * @package Netric\Authentication
 */
class AuthenticationServiceFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return FileSystem
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $key = "GENERATEDSERVERSIDEKEY";
        $userIndex = $sl->get("EntityQuery_Index");
        $userLoader = $sl->get("EntityLoader");
        $request = $sl->get("Netric/Request/Request");

        return new AuthenticationService($key, $userIndex, $userLoader, $request);
    }
}
