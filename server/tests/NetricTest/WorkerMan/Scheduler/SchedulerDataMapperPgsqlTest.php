<?php
namespace NetricTest\WorkerMan\Scheduler;

use Netric\WorkerMan\Scheduler\SchedulerDataMapperInterface;
use Netric\WorkerMan\Scheduler\SchedulerDataMapperPgsql;
use NetricTest\Bootstrap;

/**
 * DataMapper test for PGSQL
 *
 * @group integration
 */
class SchedulerDataMapperPgsqlTest extends AbstractSchedulerDataMapperTests
{
    /**
     * Create and return an instance of the pgsql datamapper
     *
     * @return SchedulerDataMapperInterface
     */
    protected function getDataMapper()
    {
        $account = Bootstrap::getAccount();
        $applicationDb = $account->getServiceManager()->get('Netric/Db/ApplicationDb');
        return new SchedulerDataMapperPgsql($applicationDb);
    }
}
