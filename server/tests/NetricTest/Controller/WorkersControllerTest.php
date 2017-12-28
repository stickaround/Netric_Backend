<?php
/**
 * Test calling the workers controller
 */
namespace NetricTest\Controller;

use Netric;
use Netric\WorkerMan\WorkerService;
use Netric\Controller\WorkersController;
use PHPUnit\Framework\TestCase;

class WorkersControllerTest extends TestCase
{
    /**
     * Account used for testing
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Controller instance used for testing
     *
     * @var WorkersController
     */
    protected $controller = null;

    /**
     * Mock worker service to interact with
     * 
     * @var WorkerService
     */
    private $workerService = null;

    /**
     * Setup the controller for tests
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Create the controller
        $this->controller = new WorkersController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;

        // Create a mock workerservice
        $this->workerService = $this->getMockBuilder(WorkerService::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->controller->setWorkerService($this->workerService);
    }

    /**
     * Make sure we can process a single job
     */
    public function testConsoleProcessAction()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setParam("runtime", 1); // Only run for 1 second
        $req->setParam("suppressoutput", 1); // Do not allow echo

        // Simulate indicating that we processed a job
        $this->workerService->method('processJobQueue')->willReturn(true);

        // Run the process action
        $ret = $this->controller->consoleProcessAction();
        $outputBuffer = $ret->getOutputBuffer();
        $this->assertContains("Processed 1 jobs", trim(array_pop($outputBuffer)));
    }

    /**
     * Test processing a scheduled job
     */
    public function testConsoleScheduleAction()
    {
        // Set params in the request
        $req = $this->controller->getRequest();
        // Not not loop indefinitely
        $req->setParam("runonce", 1);
        // Do not allow echo
        $req->setParam("suppressoutput", 1); 
        // Limit the global application lock to 1 second
        $req->setParam("locktimeout", 1); 

        // Make sure that doWorkBackground was called once
        $this->workerService->expects($this->once())
            ->method('doWorkBackground')
            ->with(
                $this->equalTo('ScheduleRunner'), 
                $this->equalTo(['account_id'=>$this->account->getId()])
            );

        // Run the process to invoke the exepects tests above
        $ret = $this->controller->consoleScheduleAction();
    }

    /**
     * Test to make sure only one instance of the scheudle action can be run
     */
    public function testConsoleScheduleAction_Locked()
    {
        $this->markTestSkipped('Skipping due to timezone problems');
        // Set params in the request
        $req = $this->controller->getRequest();
        // Do not allow echo
        $req->setParam("suppressoutput", 1); 

        
        // Make sure that doWorkBackground is ONLY CALLED ONCE
        $this->workerService->expects($this->never())
            ->method('doWorkBackground')
            ->with(
                $this->equalTo('ScheduleRunner'), 
                $this->equalTo(['account_id'=>$this->account->getId()])
            );

        // Artificially lock the test for 1 second
        $uniqueLockName = 'WorkerScheduleAction-';
        $uniqueLockName .= $this->account->getApplication()->getConfig()->version;
        $this->account->getApplication()->acquireLock($uniqueLockName, 1);
        
        // This should just exit due to the lock
        $this->controller->consoleScheduleAction();

        // It will automatically release in 1 second, but clean-up anyway
        $this->account->getApplication()->releaseLock($uniqueLockName);
    }
}
