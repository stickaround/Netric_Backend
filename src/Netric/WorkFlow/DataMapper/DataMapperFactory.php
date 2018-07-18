<?php
namespace Netric\WorkFlow\DataMapper;

use Netric\ServiceManager;
use Netric\WorkFlow\Action\ActionFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Db\Relational\RelationalDbFactory;

/**
 * Base DataMapper class
 */
class DataMapperFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $actionFactory = new ActionFactory($sl);
        $database = $sl->get(RelationalDbFactory::class);
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);
        return new WorkFlowRdbDataMapper($database, $entityLoader, $entityIndex, $actionFactory);
    }
}
