<?php
/**
 * Service factory for the Entity Definition DataMapper
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\EntityDefinition\DataMapper;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\WorkerMan\WorkerServiceFactory;
use Netric\Config\ConfigFactory;

/**
 * Create a Entity Definition DataMapper service
 */
class EntityDefinitionDataMapperFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return EntityDefinitionRdbDataMapper
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $workerService = $serviceLocator->get(WorkerServiceFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);

        return new EntityDefinitionRdbDataMapper($relationalDbCon, $workerService, $config);
    }
}
