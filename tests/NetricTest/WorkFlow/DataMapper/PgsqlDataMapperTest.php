<?php
/**
 * Test the Pgsql DataMapper for WorkFlows
 */
namespace NetricTest\WorkFlow\DataMapper;

use Netric\WorkFlow\DataMapper\WorkFlowRdbDataMapper;
use Netric\WorkFlow\Action\ActionFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Db\Relational\RelationalDbFactory;

class PgsqlDataMapperTest extends AbstractDataMapperTests
{
    public function getDataMapper()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $db = $sm->get("Db");
        $actionFactory = new ActionFactory($sm);
        $entityLoader = $sm->get("EntityLoader");
        $entityIndex = $sm->get("EntityQuery_Index");
        $database = $sm->get(RelationalDbFactory::class);
        $entityLoader = $sm->get(EntityLoaderFactory::class);
        $entityIndex = $sm->get(IndexFactory::class);
        return new WorkFlowRdbDataMapper($database, $entityLoader, $entityIndex, $actionFactory);
    }
}
