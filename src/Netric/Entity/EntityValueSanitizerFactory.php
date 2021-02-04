<?php
namespace Netric\Entity;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Account\AccountContainerFactory;

/**
 * Create a service for sanitizing entity / group values
 */
class EntityValueSanitizerFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return EntityMaintainerService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $definitionLoader = $serviceLocator->get(EntityDefinitionLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        $accountContainer = $serviceLocator->get(AccountContainerFactory::class);
        return new EntityValueSanitizer($definitionLoader, $groupingLoader, $accountContainer);
    }
}
