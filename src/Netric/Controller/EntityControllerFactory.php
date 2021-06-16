<?php

namespace Netric\Controller;

use Netric\Mvc\ControllerFactoryInterface;
use Netric\Mvc\ControllerInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Account\AccountContainerFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;
use Netric\Entity\FormsFactory;
use Netric\Permissions\DaclLoaderFactory;

/**
 * Construct the EntityController for interacting with email messages
 */
class EntityControllerFactory implements ControllerFactoryInterface
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
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $browserViewService = $serviceLocator->get(BrowserViewServiceFactory::class);
        $forms = $serviceLocator->get(FormsFactory::class);
        $daclLoader = $serviceLocator->get(DaclLoaderFactory::class);

        return new EntityController(
            $accountContainer,
            $authService,
            $entityLoader,
            $entityDefinitionLoader,
            $groupingLoader,
            $browserViewService,
            $forms,
            $daclLoader,
        );
    }
}
