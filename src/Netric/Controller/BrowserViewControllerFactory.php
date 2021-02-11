<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;

/**
 * Construct the BrowserViewController for interacting with email messages
 */
class BrowserViewControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceLocatorInterface $serviceLocator): ControllerInterface
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $authService = $serviceLocator->get(AuthenticationServiceFactory::class);
        $browserViewService = $serviceLocator->get(BrowserViewServiceFactory::class);

        return new BrowserViewController(
            $accountContainer,
            $authService,
            $browserViewService
        );
    }
}
