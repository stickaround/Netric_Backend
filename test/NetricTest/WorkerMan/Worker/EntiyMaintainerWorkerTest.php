<?php
namespace NetricTest\WorkerMan\Worker;

use Netric\WorkerMan\Worker\EntityMaintainerWorker;
use Netric\WorkerMan\Job;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class EntiyMaintainerWorkerTest extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;


    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    protected function tearDown(): void
    {
        $sl = $this->account->getServiceManager();
    }

    public function testWork()
    {
        $worker = new EntityMaintainerWorker($this->account->getApplication());
        $job = new Job();
        $job->setWorkload([
            "account_id" => $this->account->getAccountId(),
        ]);

        // Make sure it is a success
        $this->assertTrue($worker->work($job));
    }
}
