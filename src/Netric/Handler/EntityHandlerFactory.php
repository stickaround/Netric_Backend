<?php

declare(strict_types=1);

namespace Netric\Handler;

use Netric\Entity\EntityLoaderFactory;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;

/**
 * Construct the entity handler
 */
class EntityHandlerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Construct a controller and return it
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        return new EntityHandler($entityLoader);
    }
}
