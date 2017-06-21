<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2017 Aereus
 */
namespace Netric\Entity;

use Netric\ServiceManager\AccountServiceLocatorInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a service for delivering mail
 */
class EntityMaintainerServiceFactory implements AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityMaintainerService
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $log = $sl->get("Log");
        $entityLoader = $sl->get("EntityLoader");
        $entityIndex = $sl->get("EntityQuery_Index");
        $entityDefinitionLoader = $sl->get("Netric/EntityDefinitionLoader");
        return new EntityMaintainerService($log, $entityLoader, $entityDefinitionLoader, $entityIndex);
    }
}
