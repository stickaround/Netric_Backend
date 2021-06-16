<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Return data intializer
 */
class EntityTypesInitDataFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/object-types.php');
        $defDataapper = $serviceLocator->get(EntityDefinitionDataMapperFactory::class);
        $defLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        return new EntityTypesInitData($data, $defDataapper, $defLoader);
    }
}
