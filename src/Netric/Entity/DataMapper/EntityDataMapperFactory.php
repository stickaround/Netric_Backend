<?php

namespace Netric\Entity\DataMapper;

use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\Entity\ActivityLogFactory;
use Netric\Entity\EntityAggregatorFactory;
use Netric\Entity\EntityFactoryFactory;
use Netric\Entity\Notifier\NotifierFactory;
use Netric\Entity\Recurrence\RecurrenceIdentityMapperFactory;
use Netric\Entity\Validator\EntityValidatorFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntitySync\Commit\CommitManagerFactory;
use Netric\EntitySync\EntitySyncFactory;
use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\WorkerMan\WorkerServiceFactory;

/**
 * Create a Entity DataMapper service
 */
class EntityDataMapperFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityPgsqlDataMapper
     */
    public function createService(AccountServiceManagerInterface $serviceLocator)
    {
        $recurIdentityMapper = $serviceLocator->get(RecurrenceIdentityMapperFactory::class);
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);
        $entitySync = $serviceLocator->get(EntitySyncFactory::class);
        $entityValidator = $serviceLocator->get(EntityValidatorFactory::class);
        $entityFactory = $serviceLocator->get(EntityFactoryFactory::class);
        //$notifier = $serviceLocator->get(NotifierFactory::class);
        //$entityAggregator = $serviceLocator->get(EntityAggregatorFactory::class);
        $entityDefLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        //$activityLog = $serviceLocator->get(ActivityLogFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);

        return new EntityPgsqlDataMapper(
            $recurIdentityMapper,
            $commitManager,
            $entitySync,
            $entityValidator,
            $entityFactory,
            null, // $notifier,
            null, // $entityAggregator,
            $entityDefLoader,
            null, // $activityLog,
            $groupingLoader,
            $serviceLocator,
            $relationalDbCon,
            $workerService
        );
    }
}
