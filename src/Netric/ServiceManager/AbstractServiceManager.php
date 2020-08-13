<?php

namespace Netric\ServiceManager;

use Netric\Application\Application;
use RuntimeException;

/**
 * Class for constructing, caching, and finding services by name
 */
abstract class AbstractServiceManager implements ServiceLocatorInterface
{
    /**
     * Cached services that have already been constructed
     *
     * @var array
     */
    protected $loadedServices = [];

    /**
     * Optional parent used to walk up a tree
     *
     * This could be used in cases where the Applicaiton has a base
     * set of services it can load, but the account service manager
     * has it's own account specific services.
     *
     * @var ServiceLocatorInterface
     */
    protected $parentServiceLocator = null;

    /**
     * Handle to the running application
     *
     * @var Application
     */
    protected $application = null;

    /**
     * Used to track circular references
     *
     * @var array
     */
    protected $loading = [];

    /**
     * Class constructor
     *
     * We are private because the class must be a singleton to assure resources
     * are initialized only once.
     *
     * @param Application $application Handle to the running application
     * @param ServiceLocatorInterface $parentServiceLocator Optional parent for walking a tree
     */
    public function __construct(
        Application $application,
        ServiceLocatorInterface $parentServiceLocator = null
    ) {
        $this->application = $application;
        $this->parentServiceLocator = $parentServiceLocator;
    }

    /**
     * Get account instance of the running application
     *
     * @return Application
     */
    public function getApplication(): Application
    {
        return $this->application;
    }

    /**
     * Get a service by name
     *
     * @param string $serviceName
     * @return mixed The service object and false on failure
     */
    public function get($serviceName)
    {
        // First make sure something is not already trying to load this,
        // which means we have a circular refernces - muy mal!
        // if (in_array($serviceName, $this->loading)) {
        //     throw new RuntimeException(
        //         'ServiceManager Circular Reference Detected: ' .
        //             implode(' -> ', $this->loading)
        //     );
        // }
        // $currentIndex = count($this->loading);
        // $this->loading[$currentIndex] = $serviceName;
        $service = $this->initializeServiceByFactory($serviceName, true);
        // Indicate that we have finished loading
        //unset($this->loading[$currentIndex]);
        return $service;
    }

    /**
     * Clear all loaded services causing the factories to be called again
     */
    public function clearLoadedServices()
    {
        // Reset the array
        $this->loadedServices = [];
    }

    /**
     * Attempt to initialize a service by loading a factory
     *
     * @param string $serviceName The class name of the service to load
     * @param bool $bCache Flag to enable caching this service for future requests
     * @throws Exception\ServiceNotFoundException Could not autoload factory for named service
     * @return mixed Service instance if loaded, null if class not found
     */
    private function initializeServiceByFactory($serviceName, $bCache = true)
    {
        // Normalise the serviceName
        $serviceName = $this->normalizeClassPath($serviceName);

        // Get actual class name by appending 'Factory' and normalizing slashes
        $factoryClassPath = $this->getServiceFactoryPath($serviceName);

        // Check to see if the service was already loaded
        if ($this->isLoaded($factoryClassPath)) {
            return $this->loadedServices[$factoryClassPath];
        }

        // Check the parent if it was already loaded
        if ($this->parentServiceLocator) {
            if ($this->parentServiceLocator->isLoaded($factoryClassPath)) {
                return $this->parentServiceLocator->get($factoryClassPath);
            }
        }

        // Load the the service for the first time
        $service = null;

        // Try to load the service and allow exception to be thrown if not found
        if ($factoryClassPath) {
            if (class_exists($factoryClassPath)) {
                $factory = new $factoryClassPath();
            } else {
                throw new Exception\ServiceNotFoundException(sprintf(
                    '%s: A service by the name "%s" was not found and could not be instantiated.',
                    get_class($this) . '::' . __FUNCTION__,
                    $factoryClassPath
                ));
            }

            if ($factory instanceof ServiceFactoryInterface) {
                $service = $factory->createService($this);
            } else {
                throw new Exception\ServiceNotFoundException(sprintf(
                    '%s: The factory interface must implement Netric/ServiceManager/AccountServiceFactoryInterface.',
                    get_class($this) . '::' . __FUNCTION__,
                    $factoryClassPath
                ));
            }
        }

        // Cache for future calls
        if ($bCache) {
            $this->loadedServices[$factoryClassPath] = $service;
        }

        return $service;
    }

    /**
     * Normalize class path
     *
     * @param string $classPath The unique name of the service to load
     * @return string Autoloader friendly class path
     */
    private function normalizeClassPath($classPath)
    {
        // Replace forward slash with backslash
        $classPath = str_replace('/', '\\', $classPath);

        // If class begins with "\Netric" then remove the first slash because it is not needed
        if ("\\Netric" == substr($classPath, 0, strlen("\\Netric"))) {
            $classPath = substr($classPath, 1);
        }

        return $classPath;
    }

    /**
     * Try to locate service loading factory from the service path
     *
     * @param string $sServiceName The unique name of the service to load
     * @return string|bool The real path to the service factory class, or false if class not found
     */
    private function getServiceFactoryPath($sServiceName)
    {
        // Append Factory to the service name if not already at the end
        $numCharsInFactory = 7;
        if (strlen($sServiceName) > $numCharsInFactory) {
            if (substr($sServiceName, $numCharsInFactory * -1) != 'Factory') {
                return $sServiceName .= 'Factory';
            }
        }

        // Just append factory since the word is too short to have it
        if (strlen($sServiceName) < $numCharsInFactory) {
            $sServiceName .= 'Factory';
        }

        return $sServiceName;
    }

    /**
     * Check to see if a service is already loaded
     *
     * @param string $serviceName
     * @return bool true if service is loaded and cached, false if it needs to be instantiated
     */
    public function isLoaded($serviceName)
    {
        if (
            isset($this->loadedServices[$serviceName])
            && $this->loadedServices[$serviceName] != null
        ) {
            return true;
        }

        return false;
    }
}
