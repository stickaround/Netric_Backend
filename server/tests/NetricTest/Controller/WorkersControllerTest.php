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
     * Setup the controller for tests
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Create the controller
        $this->controller = new WorkersController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;
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

        // Setup a test job
        $appServiceManager = $this->account->getApplication()->getServiceManager();
        $workerService = $appServiceManager->get(WorkerService::class);
        $workerService->doWorkBackground("Test", array("mystring"=>"test"));

        // Run the process action
        $ret = $this->controller->consoleProcessAction();
        $this->assertInstanceOf(
            'Netric\Application\Response\ConsoleResponse',
            $ret
        );
        $outputBuffer = $ret->getOutputBuffer();
        $this->assertEquals("Processed 1 jobs", trim(array_pop($outputBuffer)));
    }

    /**
     * Test processing a scheduled job
     */
    public function testConsoleScheduleAction()
    {
        $this->markTestIncomplete('Need to do this');
    }
}
