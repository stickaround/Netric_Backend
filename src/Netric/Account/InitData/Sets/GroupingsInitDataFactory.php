<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Return data intializer
 */
class GroupingsInitDataFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/groupings.php');
        $groupingsLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        return new GroupingsInitData($data, $groupingsLoader);
    }
}
