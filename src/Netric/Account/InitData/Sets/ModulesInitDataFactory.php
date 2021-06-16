<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Module\ModuleServiceFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Return data intializer
 */
class ModulesInitDataFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/modules.php');
        $moduleDataDir = __DIR__ . '/../../../../../data/modules';
        $moduleService = $serviceLocator->get(ModuleServiceFactory::class);
        return new ModulesInitData($data, $moduleDataDir, $moduleService);
    }
}
