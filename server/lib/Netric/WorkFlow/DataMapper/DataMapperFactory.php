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

/**
 * Base DataMapper class
 */
class DataMapperFactory implements ServiceManager\ServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\ServiceLocatorInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(ServiceManager\ServiceLocatorInterface $sl)
    {
        $db = $sl->get("Db");
        $actionFactory = new ActionFactory($sl);
        $entityLoader = $sl->get("EntityLoader");
        $entityIndex = $sl->get("EntityQuery_Index");

        // Right now we only support PgSql but may support more later
        return new PgsqlDataMapper($db, $actionFactory, $entityLoader, $entityIndex);
    }
}
