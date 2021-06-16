<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\EntityDefinition\DataMapper\EntityDefinitionDataMapperFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Return data intializer
 */
class EntityTypesInitDataFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/object-types.php');
        $defDataapper = $serviceLocator->get(EntityDefinitionDataMapperFactory::class);
        $defLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        return new EntityTypesInitData($data, $defDataapper, $defLoader);
    }
}
