<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module;

use Netric\ServiceManager;
use Netric\Account\Module\DataMapper\DataMapperFactory;

/**
 * Create a module service
 */
class ModuleServiceFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return ModuleService
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $dataMapper = $sl->get(DataMapperFactory::class);

        return new ModuleService($dataMapper);
    }
}
