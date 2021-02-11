<?php

declare(strict_types=1);

namespace Netric\EntitySync;

use Netric\Db\Relational\RelationalDbContainer;
use Netric\EntitySync\Collection\CollectionFactory;
use Netric\WorkerMan\WorkerService;
use Netric\DataMapperAbstract;

abstract class AbstractDataMapper extends DataMapperAbstract
{
    /**
     * Class constructor
     *
    * @param RelationalDbContainer $databaseContainer Used to get active database connection for the right account
     * @param EntityCollection $entityCollection Collection that will be used to Sync Entities
     * @param WorkerService $workerService Used to schedule background jobs
     */
    public function __construct(
        RelationalDbContainer $dbContainer,
        WorkerService $workerService,
        CollectionFactory $collectionFactory
    ) {
        $this->setUp($dbContainer, $workerService, $collectionFactory);
    }
}
