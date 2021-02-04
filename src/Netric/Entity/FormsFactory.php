<?php

namespace Netric\Entity;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Config\ConfigFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;

/**
 * Service factory for the Forms
 */
class FormsFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return Forms
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);

        return new Forms($relationalDbCon, $config);
    }
}
