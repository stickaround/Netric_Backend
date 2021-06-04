<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Permissions\DaclLoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;

/**
 * Construct the PermissionController for interacting with email messages
 */
class PermissionControllerFactory implements ControllerFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceContainerInterface $serviceLocator
     * @return ControllerInterface
     */
    public function get(ServiceContainerInterface $serviceLocator): ControllerInterface
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $authService = $serviceLocator->get(AuthenticationServiceFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $daclLoader = $serviceLocator->get(DaclLoaderFactory::class);

        return new PermissionController(
            $accountContainer,
            $authService,
            $entityLoader,
            $entityDefinitionLoader,
            $groupingLoader,
            $daclLoader
        );
    }
}
