<?php

namespace Netric\Entity;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Config\ConfigFactory;
use Netric\Db\Relational\RelationalDbContainerFactory;

/**
 * Service factory for the Forms
 */
class FormsFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return Forms
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $relationalDbCon = $serviceLocator->get(RelationalDbContainerFactory::class);
        $config = $serviceLocator->get(ConfigFactory::class);

        return new Forms($relationalDbCon, $config);
    }
}
