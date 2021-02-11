<?php

/**
 * Test the module controller
 */

namespace NetricTest\Controller;

use Netric;
use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\Controller\ModuleController;
use Netric\Account\Module\ModuleService;
use Netric\Account\Module\Module;
use Ramsey\Uuid\Uuid;

/**
 * @group integration
 */
class ModuleControllerTest extends TestCase
{
    /**
     * Initialized controller with mock dependencies
     */
    private ModuleController $moduleController;

    /**
     * Dependency mocks
     */
    private AuthenticationService $mockAuthService;
    private Account $mockAccount;
    private ModuleService $moduleService;

    protected function setUp(): void
    {
        // Create mocks
        $this->moduleService = $this->createMock(ModuleService::class);

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
        $this->moduleController = new ModuleController(
            $this->accountContainer,
            $this->mockAuthService,
            $this->moduleService
        );

        $this->moduleController->testMode = true;
    }

    /**
     * Test getting a module using module name
     */
    public function testGetGetAction()
    {
        $moduleId = Uuid::uuid4()->toString();
        $moduleData = [
            'id' => $moduleId,
            'name' => 'test_module',
            'title' => 'Test Module'
        ];

        $testModule = new Module();
        $testModule->fromArray($moduleData);

        $this->moduleService->method('getByName')->willReturn($testModule);

        // Make sure getGetAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setParam('moduleName', 'test_module');
        $response = $this->moduleController->getGetAction($request);
        $this->assertEquals($testModule->toArray(), $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in getting a module
     */
    public function testGetGetActionCatchingErrors()
    {
        // Make sure getGetAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setParam('bogus', 'data');
        $response = $this->moduleController->getGetAction($request);
        $this->assertEquals(['error' => 'moduleName is a required query param.'], $response->getOutputBuffer());
    }

    /**
     * Test saving a module
     */
    public function testSaveAction()
    {
        $moduleId = Uuid::uuid4()->toString();
        $moduleData = [
            'id' => $moduleId,
            'name' => 'test_module',
            'title' => 'Test Module'
        ];

        $testModule = new Module();
        $testModule->fromArray($moduleData);

        $this->moduleService->method('createNewModule')->willReturn(new Module());
        $this->moduleService->method('save')->willReturn(true);

        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode($moduleData));
        $response = $this->moduleController->postSaveAction($request);
        $this->assertEquals($testModule->toArray(), $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in saving a module
     */
    public function testSaveActionCatchingErrors()
    {
        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->moduleController->postSaveAction($request);
        $this->assertEquals(['error' => 'name is a required param.'], $response->getOutputBuffer());

        $moduleId = Uuid::uuid4()->toString();
        $moduleData = [
            'id' => $moduleId,
            'name' => 'test_module',
            'title' => 'Test Module'
        ];

        $testModule = new Module();
        $testModule->fromArray($moduleData);

        $this->moduleService->method('createNewModule')->willReturn(new Module());
        $this->moduleService->method('save')->willReturn(false);

        // Test if there is problem when saving a module, it should return the error
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode($moduleData));
        $response = $this->moduleController->postSaveAction($request);
        $this->assertEquals(['error' => 'Error saving the module.'], $response->getOutputBuffer());
    }

    /**
     * Test deleting a module
     */
    public function testDeleteAction()
    {
        $moduleId = Uuid::uuid4()->toString();
        $moduleData = [
            'id' => $moduleId,
            'name' => 'test_module',
            'title' => 'Test Module'
        ];

        $testModule = new Module();
        $testModule->fromArray($moduleData);

        $this->moduleService->method('getById')->willReturn($testModule);
        $this->moduleService->method('delete')->willReturn(true);

        // Make sure postDeleteAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(["id" => $moduleId]));
        $response = $this->moduleController->postDeleteAction($request);
        $this->assertEquals(true, $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in deleting a module
     */
    public function testDeleteActionCatchingErrors()
    {
        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->moduleController->postDeleteAction($request);
        $this->assertEquals(['error' => 'id is a required param.'], $response->getOutputBuffer());

        $moduleId = Uuid::uuid4()->toString();
        $moduleData = [
            'id' => $moduleId,
            'name' => 'test_module',
            'title' => 'Test Module'
        ];

        $testModule = new Module();
        $testModule->fromArray($moduleData);

        $this->moduleService->method('getById')->willReturn($testModule);
        $this->moduleService->method('delete')->willReturn(false);

        // Test if there is problem when deleting a module, it should return the error
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(["id" => $moduleId]));
        $response = $this->moduleController->postDeleteAction($request);
        $this->assertEquals(['error' => 'Error while trying to delete the module.'], $response->getOutputBuffer());
    }

    public function testGetGetAvailableModulesAction()
    {
        $moduleId1 = Uuid::uuid4()->toString();
        $moduleData1 = [
            'id' => $moduleId1,
            'name' => 'test_module',
            'title' => 'Test Module'
        ];

        $moduleId2 = Uuid::uuid4()->toString();
        $moduleData2 = [
            'id' => $moduleId2,
            'name' => 'test_module',
            'title' => 'Test Module'
        ];

        $testModule1 = new Module();
        $testModule1->fromArray($moduleData1);

        $testModule2 = new Module();
        $testModule2->fromArray($moduleData1);

        $this->moduleService->method('getForUser')->willReturn([$testModule1, $testModule2]);

        // Make sure getGetAvailableModulesAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->moduleController->getGetAvailableModulesAction($request);
        $this->assertEquals([$testModule1->toArray(), $testModule2->toArray()], $response->getOutputBuffer());
    }
}
