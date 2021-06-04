<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\EntityGroupings\GroupingLoaderFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Return data intializer
 */
class GroupingsInitDataFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/groupings.php');
        $groupingsLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        return new GroupingsInitData($data, $groupingsLoader);
    }
}
