<?php
namespace Netric\Entity\DataMapper;

use Netric\ServiceManager\AccountServiceLocatorInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a Entity DataMapper service
 */
class DataMapperFactory implements AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $db = $sl->get("Netric/Db/Db");
        return new Pgsql($sl->getAccount(), $db);

        //return new EntityRdbDataMapper($sl->getAccount());
    }
}
