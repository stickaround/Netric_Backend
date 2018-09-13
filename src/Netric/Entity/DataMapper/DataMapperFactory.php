<?php
namespace Netric\Entity\DataMapper;

use Netric\ServiceManager\AccountServiceFactoryInterface;
use Netric\ServiceManager\AccountServiceManagerInterface;

/**
 * Create a Entity DataMapper service
 */
class DataMapperFactory implements AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapperInterface
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        return new EntityRdbDataMapper($sl->getAccount());
    }
}
