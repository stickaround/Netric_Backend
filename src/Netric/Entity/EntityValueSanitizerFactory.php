<?php
namespace Netric\Entity;

use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\Account\AccountContainerFactory;

/**
 * Create a service for sanitizing entity / group values
 */
class EntityValueSanitizerFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityMaintainerService
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $definitionLoader = $sl->get(EntityDefinitionLoaderFactory::class);
        $groupingLoader = $sl->get(GroupingLoaderFactory::class);
        $accountContainer = $sl->get(AccountContainerFactory::class);
        return new EntityValueSanitizer($definitionLoader, $groupingLoader, $accountContainer);
    }
}
