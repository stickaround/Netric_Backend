<?php

declare(strict_types=1);

namespace Netric\EntitySync\Commit;

use Netric\ServiceManager;
use Netric\ServiceManager\AccountServiceManagerInterface;
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
     * @param AccountServiceManagerInterface $sl ServiceLocator for injecting dependencies
     * @return CommitManager
     */
    public function createService(AccountServiceManagerInterface $sl)
    {
        $dm = $sl->get(DataMapperFactory::class);
        return new CommitManager($dm);
    }
}
