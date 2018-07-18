<?php
namespace Netric\WorkFlow\DataMapper;

use Netric\ServiceManager;
use Netric\WorkFlow\Action\ActionFactory;

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
        return new WorkFlowRdbDataMapper($sl->getAccount(), $actionFactory);
    }
}
