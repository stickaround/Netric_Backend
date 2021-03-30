<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Module\ModuleServiceFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Return data intializer
 */
class ModulesInitDataFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/modules.php');
        $moduleDataDir = __DIR__ . '/../../../../../data/modules';
        $moduleService = $serviceLocator->get(ModuleServiceFactory::class);
        return new ModulesInitData($data, $moduleDataDir, $moduleService);
    }
}
