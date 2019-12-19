<?php
/**
 * Controller for FilesControllerFactory interactoin
 */

namespace Netric\Controller;
use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Construct the FilesController for itneracting with upload and download files
 */
class FilesControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceLocatorInterface $serviceLocator): ControllerInterface
    {   
        $application = $serviceLocator->getApplication();
        $account = $application->getAccount();
        return new FilesController($application, $account);
    }
}
