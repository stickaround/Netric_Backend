<?php
namespace Netric\EntityGroupings\DataMapper;

use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceLocatorInterface;

/**
 * Create a EntityGroupings DataMapper service
 */
class EntityGroupingDataMapperFactory implements AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $db = $sl->get("Netric/Db/Relational/RelationalDb");
        return new EntityGroupingRdbDataMapper($sl->getAccount(), $db);
    }
}
