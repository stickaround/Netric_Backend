<?php
namespace NetricTest\WorkerMan;

use Netric\WorkerMan\SchedulerService;
use PHPUnit\Framework\TestCase;
use DateTime;

/**
 * Class SchedulerServiceTest
 *
 * Validate that we can schedule workers
 *
 * @package NetricTest\WorkerMan
 */
class SchedulerServiceTest extends TestCase
{
    /**
     * Scheduler service to test
     *
     * @var SchedulerService
     */
    private $scheduler = null;

    /**
     * Setup the service
     */
    protected function setUp()
    {
        $this->scheduler = new SchedulerService();
    }

    public function testScheduleAtTime()
    {
        $now = new DateTime();
        $id = $this->scheduler->scheduleAtTime('Test', $now, ['myvar'=>'testval']);
        $this->assertNotNull($id);
    }

    public function testScheduleAtInterval()
    {

    }

    public function testGetScheduledToRun()
    {

    }

    public function testMarkCompleted()
    {

    }
}