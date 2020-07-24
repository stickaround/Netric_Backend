<?php

/**
 * Make sure the bin/scripts/update/always/06-worker-jobs.php script works
 */
namespace BinTest\Update\Always;

use Netric\WorkerMan\SchedulerService;
use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityQuery\Index\IndexInterface;

use DateTime;
use DateInterval;

/**
 * @group integration
 */
class WorkerJobsTest extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Path to the script to test
     *
     * @var string
     */
    private $scriptPath = null;

    /**
     * Entity index for querying
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $serviceManager = $this->account->getServiceManager();
        $this->schedulerService = $serviceManager->get(SchedulerService::class);
        $this->entityIndex = $serviceManager->get(IndexFactory::class);
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/always/06-worker-jobs.php";
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right, but why
     * not just use unit tests for our tests? :)
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }

    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testRun()
    {
        $scheduledJobsData = require(__DIR__ . "/../../../../data/account/worker-jobs.php");
        $binScript = new BinScript($this->account->getApplication(), $this->account);

        // Run the script which should add all system scheduled jobs
        $this->assertTrue($binScript->run($this->scriptPath));

        // Make sure that each of the workers was scheduled
        foreach ($scheduledJobsData as $jobToSchedule) {
            $query = new EntityQuery("worker_job");
            $query->where('worker_name', $jobToSchedule['worker_name']);
            $result = $this->entityIndex->executeQuery($query);
            $this->assertGreaterThanOrEqual(1, $result->getTotalNum());
        }
    }

    /**
     * Make sure that we do not create multiple scheduled jobs if already scheduled
     */
    public function testRunNoDuplicates()
    {
        $scheduledJobsData = require(__DIR__ . "/../../../../data/account/worker-jobs.php");
        $binScript = new BinScript($this->account->getApplication(), $this->account);

        // Create a future date 3 days from now (most jobs are every day at the latest)
        $future = new DateTime();
        $future->add(new DateInterval("P3D"));

        // Manually add one of the jobs
        $this->schedulerService->scheduleAtInterval(
            $scheduledJobsData[0]['worker_name'],
            $scheduledJobsData[0]['job_data'],
            $scheduledJobsData[0]['recurrence']['type'],
            $scheduledJobsData[0]['recurrence']['interval']
        );

        // Get jobs scheduled up to $future
        $jobs = $this->schedulerService->getScheduledToRun(
            $future,
            $scheduledJobsData[0]['worker_name']
        );
        $numBeforeRunningBinScript = count($jobs);

        // Run the update script which should not create any duplicates
        $this->assertTrue($binScript->run($this->scriptPath));

        // Make sure no duplicates were created
        $jobs = $this->schedulerService->getScheduledToRun(
            $future,
            $scheduledJobsData[0]['worker_name']
        );

        // The number should remain unchanged
        $this->assertEquals($numBeforeRunningBinScript, count($jobs));
    }
}
