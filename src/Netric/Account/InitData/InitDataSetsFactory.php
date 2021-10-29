<?php

declare(strict_types=1);

namespace Netric\Account\InitData;

use Netric\Account\InitData\Sets\EmailAccountsInitDataFactory;
use Netric\Account\InitData\Sets\EmailDomainInitDataFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Account\InitData\Sets\EntityTypesInitDataFactory;
use Netric\Account\InitData\Sets\GroupingsInitDataFactory;
use Netric\Account\InitData\Sets\UsersInitDataFactory;
use Netric\Account\InitData\Sets\ModulesInitDataFactory;
use Netric\Account\InitData\Sets\RootFolderInitDataFactory;
use Netric\Account\InitData\Sets\WorkerJobsInitDataFactory;
use Netric\Account\InitData\Sets\WorkflowsInitData;

/**
 * Return array of data initializers to run for an account
 */
class InitDataSetsFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        // Return array of importers to be executed in order
        return [
            $serviceLocator->get(EntityTypesInitDataFactory::class),
            $serviceLocator->get(GroupingsInitDataFactory::class),
            $serviceLocator->get(RootFolderInitDataFactory::class),
            $serviceLocator->get(UsersInitDataFactory::class),
            $serviceLocator->get(ModulesInitDataFactory::class),
            $serviceLocator->get(WorkerJobsInitDataFactory::class),
            $serviceLocator->get(WorkflowsInitData::class),
            $serviceLocator->get(EmailAccountsInitDataFactory::class),
            $serviceLocator->get(EmailDomainInitDataFactory::class)
        ];
    }
}
