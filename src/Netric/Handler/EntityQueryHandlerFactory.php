<?php

declare(strict_types=1);

namespace Netric\Handler;

use Netric\Account\AccountContainerFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Permissions\DaclLoaderFactory;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;

/**
 * Construct the entity query handler
 */
class EntityQueryHandlerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        $daclLoader = $serviceLocator->get(DaclLoaderFactory::class);
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        return new EntityQueryHandler(
            $accountContainer,
            $daclLoader,
            $entityIndex,
            $entityLoader
        );
    }
}
