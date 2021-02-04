<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module;

use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Account\Module\DataMapper\DataMapperFactory;

/**
 * Create a module service
 */
class ModuleServiceFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return ModuleService
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $dataMapper = $serviceLocator->get(DataMapperFactory::class);

        return new ModuleService($dataMapper);
    }
}
