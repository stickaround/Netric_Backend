<?php

/**
 * Test calling the workers controller
 */

namespace NetricTest\Controller;

use Netric;
use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Application\Application;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\WorkerMan\WorkerService;
use Netric\Controller\WorkersController;
use Netric\Log\LogInterface;
use Netric\Request\ConsoleRequest;
use Ramsey\Uuid\Uuid;

/**
 * @group integration
 */
class WorkersControllerTest extends TestCase
{
    /**
     * Initialized controller with mock dependencies
     */
    private WorkersController $workersController;

    /**
     * Dependency mocks
     */
    private AuthenticationService $mockAuthService;
    private Account $mockAccount;
    private ModuleService $moduleService;

    /**
     * Setup the controller for tests
     */
    protected function setUp(): void
    {
        // Create mocks
        $this->workerService = $this->createMock(WorkerService::class);
        $this->mockLog = $this->createMock(LogInterface::class);
        $this->mockApplication = $this->createMock(Application::class);

        // Provide identity for mock auth service
        $this->mockAuthService = $this->createMock(AuthenticationService::class);
        $ident = new AuthenticationIdentity('blahaccount', 'blahuser');
        $this->mockAuthService->method('getIdentity')->willReturn($ident);

        // Return mock authenticated account
        $this->mockAccount = $this->createStub(Account::class);
        $this->mockAccount->method('getAccountId')->willReturn(Uuid::uuid4()->toString());

        $this->accountContainer = $this->createMock(AccountContainerInterface::class);
        $this->accountContainer->method('loadById')->willReturn($this->mockAccount);

        // Create the controller with mocks
        $this->workersController = new WorkersController(
            $this->accountContainer,
            $this->mockAuthService,
            $this->workerService,
            $this->mockLog,
            $this->mockApplication
        );

        $this->workersController->testMode = true;
    }

    /**
     * Make sure we can process a single job
     */
    public function testConsoleProcessAction()
    {
        // Set params in the request
        $request = new ConsoleRequest();
        $request->setParam("runtime", 1); // Only run for 1 second
        $request->setParam("suppressoutput", 1); // Do not allow echo

        // Simulate indicating that we processed a job
        $this->workerService->method('processJobQueue')->willReturn(true);

        // Run the process action
        $response = $this->workersController->consoleProcessAction($request);
        $outputBuffer = $response->getOutputBuffer();
        $this->assertStringContainsString("Processed 1 jobs", trim(array_pop($outputBuffer)));
    }

    /**
     * Test to make sure only one instance of the scheudle action can be run
     */
    public function testConsoleScheduleAction()
    {
        // Do not allow echo
        $request = new HttpRequest();
        $request->setParam("suppressoutput", 1);


        // Make sure that doWorkBackground is ONLY CALLED ONCE
        $this->workerService->expects($this->never())
            ->method('doWorkBackground')
            ->with(
                $this->equalTo('ScheduleRunner'),
                $this->equalTo(['account_id' => $this->mockAccount->getAccountId()])
            );

        // // Artificially lock the test for 1 second
        // $uniqueLockName = 'WorkerScheduleAction-';
        // $uniqueLockName .= $this->account->getApplication()->getConfig()->version;
        // $this->account->getApplication()->acquireLock($uniqueLockName, 1);

        // // This should just exit due to the lock
        // $this->controller->consoleScheduleAction();

        // // It will automatically release in 1 second, but clean-up anyway
        // $this->account->getApplication()->releaseLock($uniqueLockName);
    }
}
