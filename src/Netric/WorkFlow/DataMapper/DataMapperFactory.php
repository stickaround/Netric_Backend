<?php

/**
 * Service factory for setting up the WorkFlow datamapper
 *
 * @author Sky Stebnicki, sky.stebnicki@aereus.com
 * @copyright Copyright (c) 2015 Aereus Corporation (http://www.aereus.com)
 */
namespace Netric\WorkFlow\DataMapper;

use Netric\ServiceManager;
use Netric\WorkFlow\Action\ActionFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityLoaderFactory;
use Netric\Db\DbFactory;

/**
 * Base DataMapper class
 */
class DataMapperFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $db = $sl->get(DbFactory::class);
        $actionFactory = new ActionFactory($sl);
        $entityLoader = $sl->get(EntityLoaderFactory::class);
        $entityIndex = $sl->get(IndexFactory::class);

        // Right now we only support PgSql but may support more later
        return new PgsqlDataMapper($db, $actionFactory, $entityLoader, $entityIndex);
    }
}
