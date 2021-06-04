<?php

declare(strict_types=1);

namespace Netric\Account\InitData;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Account\InitData\Sets\EntityTypesInitDataFactory;
use Netric\Account\InitData\Sets\GroupingsInitDataFactory;
use Netric\Account\InitData\Sets\UsersInitDataFactory;
use Netric\Account\InitData\Sets\ModulesInitDataFactory;
use Netric\Account\InitData\Sets\RootFolderInitDataFactory;
use Netric\Account\InitData\Sets\WorkerJobsInitDataFactory;

/**
 * Return array of data initializers to run for an account
 */
class InitDataSetsFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        // Return array of importers to be executed in order
        return [
            $serviceLocator->get(EntityTypesInitDataFactory::class),
            $serviceLocator->get(GroupingsInitDataFactory::class),
            $serviceLocator->get(RootFolderInitDataFactory::class),
            $serviceLocator->get(UsersInitDataFactory::class),
            $serviceLocator->get(ModulesInitDataFactory::class),
            $serviceLocator->get(WorkerJobsInitDataFactory::class)
        ];
    }
}
