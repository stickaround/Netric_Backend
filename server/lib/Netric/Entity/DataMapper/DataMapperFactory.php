<?php
/**
 * Service factory for the Entity DataMapper
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\Entity\DataMapper;

use Netric\ServiceManager;

/**
 * Create a Entity DataMapper service
 *
 * @package DataMapperInterface
 */
class DataMapperFactory implements ServiceManager\AccountServiceLocatorInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return DataMapper/Pgsql
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $db = $sl->get("Db");
        return new Pgsql($sl->getAccount(), $db);
    }
}
