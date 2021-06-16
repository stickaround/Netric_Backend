<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Entity\EntityLoaderFactory;
use Aereus\ServiceContainer\FactoryInterface;
use Aereus\ServiceContainer\ServiceContainerInterface;

/**
 * Return data intializer
 */
class UsersInitDataFactory implements FactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceContainerInterface ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function __invoke(ServiceContainerInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/users.php');
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        return new UsersInitData($data, $entityLoader);
    }
}
