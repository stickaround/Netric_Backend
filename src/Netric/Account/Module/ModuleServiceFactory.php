<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module;

use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;
use Netric\Account\Module\DataMapper\DataMapperFactory;

/**
 * Create a module service
 */
class ModuleServiceFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return ModuleService
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $dataMapper = $serviceLocator->get(DataMapperFactory::class);

        return new ModuleService($dataMapper);
    }
}
