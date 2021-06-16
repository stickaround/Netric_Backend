<?php
namespace Netric\Entity;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Account\AccountContainerFactory;

/**
 * Create a service for sanitizing entity / group values
 */
class EntityValueSanitizerFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return EntityMaintainerService
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $definitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        return new EntityValueSanitizer($definitionLoader, $groupingLoader, $accountContainer);
    }
}
