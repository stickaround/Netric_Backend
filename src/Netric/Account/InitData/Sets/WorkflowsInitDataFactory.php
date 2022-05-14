<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Entity\EntityLoaderFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\ServiceManager\ApplicationServiceFactoryInterface;
use Netric\ServiceManager\ServiceLocatorInterface;

/**
 * Return data intializer
 */
class WorkflowsInitDataFactory implements ApplicationServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @return InitDataInterface[]
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $data = require(__DIR__ . '/../../../../../data/account/workflows.php');
        $entityIndex = $serviceLocator->get(IndexFactory::class);
        $entityLoader = $serviceLocator->get(EntityLoaderFactory::class);
        $groupingLoader = $serviceLocator->get(GroupingLoaderFactory::class);
        return new WorkflowsInitData(
            $data,
            $entityIndex,
            $entityLoader,
            $groupingLoader
        );
    }
}
