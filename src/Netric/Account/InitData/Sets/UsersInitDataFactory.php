<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Entity\EntityLoaderFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Return data intializer
 */
class UsersInitDataFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/users.php');
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        return new UsersInitData($data, $entityLoader);
    }
}
