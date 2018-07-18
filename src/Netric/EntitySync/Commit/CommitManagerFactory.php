<?php
/**
 * Service factory for the Entity Sync Commit Manager
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */
namespace Netric\EntitySync\Commit;

use Netric\ServiceManager;
use Netric\EntitySync\Commit\DataMapper\DataMapperFactory;

/**
 * Create a Entity Sync Commit Manager service
 *
 * @package Netric\EntitySync\Commit\CommitManager
 */
class CommitManagerFactory implements ServiceManager\AccountServiceFactoryInterface
{
    /**
     * Service creation factory
     *
     * @param ServiceManager\AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return CommitManager
     */
    public function createService(ServiceManager\AccountServiceManagerInterface $sl)
    {
        $dm = $sl->get(DataMapperFactory::class);
        return new CommitManager($dm);
    }
}