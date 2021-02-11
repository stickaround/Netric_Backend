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
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\WorkerMan\WorkerServiceFactory;

/**
 * Create a Entity DataMapper service
 */
class EntityDataMapperFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityPgsqlDataMapper
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $recurIdentityMapper = $serviceLocator->get(RecurrenceIdentityMapperFactory::class);
        $commitManager = $serviceLocator->get(CommitManagerFactory::class);
        $entityValidator = $serviceLocator->get(EntityValidatorFactory::class);
        $entityFactory = $serviceLocator->get(EntityFactoryFactory::class);
        $entityDefLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);

        return new EntityPgsqlDataMapper(
            $recurIdentityMapper,
            $commitManager,
            null, // $entitySync,
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
