<?php

namespace Netric\Entity;

use Netric\ServiceManager;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a new EntityFactory service
 */
class EntityFactoryFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return EntityFactory
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        return new EntityFactory($sl);
    }
}
