<?php
/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Account\Module;

use Netric\ServiceManager;

/**
 * Create a module service
 */
class ModuleServiceFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param \Netric\ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return ModuleService
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $dataMapper = $sl->get('Netric/Account/Module/DataMapper/DataMapper');
        return new ModuleService($dataMapper);
    }
}
