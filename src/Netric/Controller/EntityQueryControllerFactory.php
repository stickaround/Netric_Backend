<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Permissions\DaclLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Controller\EntityQueryController;

/**
 * Construct the EntityQueryController for interacting with email messages
 */
class EntityQueryControllerFactory implements ControllerFactoryInterface
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
        $daclLoader = $serviceLocator->get(DaclLoaderFactory::class);
        $index = $serviceLocator->get(IndexFactory::class);

        return new EntityQueryController(
            $accountContainer,
            $authService,
            $daclLoader,
            $index
        );
    }
}
