<?php
/**
 * Service factory for the EntityQuery Index
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\EntityQuery\Index;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityFactoryFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\EntityValueSanitizerFactory;

/**
 * Create a EntityQuery Index service
 */
class IndexFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return IndexInterface
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $entityFactory = $serviceLocator->get(EntityFactoryFactory::class);
        $entityDefinitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $entityValueSanitizer = $serviceLocator->get(EntityValueSanitizerFactory::class);

        return new EntityQueryIndexRdb(
            $relationalDbCon,
            $entityFactory,
            $entityDefinitionLoader,
            $entityLoader,
            $entityValueSanitizer,
            $serviceLocator
        );
    }
}
