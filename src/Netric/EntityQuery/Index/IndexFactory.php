<?php
/**
 * Service factory for the EntityQuery Index
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\EntityQuery\Index;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Db\Relational\RelationalDbContainerFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Entity\EntityFactoryFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\EntityValueSanitizerFactory;

/**
 * Create a EntityQuery Index service
 */
class IndexFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return IndexInterface
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
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
