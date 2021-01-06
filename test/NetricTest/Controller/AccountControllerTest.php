<?php

/**
 * Test the account controller
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
use Netric\Controller\AccountController;
use Netric\Account\Module\Module;
use Netric\Account\Module\ModuleService;
use Netric\Account\Billing\AccountBillingService;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\CustomerEntity;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;

/**
 * @group integration
 */
class AccountControllerTest extends TestCase
{
    /**
     * Initialized controller with mock dependencies
     */
    private AccountController $accountController;

    /**
     * Dependency mocks
     */
    private AuthenticationService $mockAuthService;    
    private Account $mockAccount;
    private ModuleService $mockModuleService;
    private AccountBillingService $accountBillingService;
    private EntityLoader $mockEntityLoader;
    private AccountContainerInterface $accountContainer;

    protected function setUp(): void
    {
        // Create mocks
        $this->mockModuleService = $this->createMock(ModuleService::class);
        $this->accountBillingService = $this->createMock(AccountBillingService::class);
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);

        // Provide identity for mock auth service
        $this->mockAuthService = $this->createMock(AuthenticationService::class);
        $ident = new AuthenticationIdentity('blahaccount', 'blahuser');
        $this->mockAuthService->method('getIdentity')->willReturn($ident);

        // Return mock authenticated account
        $this->mockAccount = $this->createStub(Account::class);
        $this->mockAccount->method('getAccountId')->willReturn(Uuid::uuid4()->toString());

        $this->accountContainer = $this->createMock(AccountContainerInterface::class);
        $this->accountContainer->method('loadById')->willReturn($this->mockAccount);
        $this->accountContainer->method('updateAccount')->willReturn(true);
        
        // Create the controller with mocks
        $this->accountController = new AccountController(
            $this->accountContainer,
            $this->mockAuthService,
            $this->mockEntityLoader,
            $this->mockModuleService,
            $this->accountBillingService
        );

        $this->accountController->testMode = true;
    }

    /**
     * Test the getting the account
     */
    public function testGetAction()
    {
        $moduleData = [
            "default_route" => "activity",
            "icon" => "HomeIcon",
            "id" => 50,
            "name" => "home",
            "scope" => "system",
            "short_title" => "Home",
            "sort_order" => 1,
            "system" => true,
            "title" => "Home"
        ];

        // Create test home module
        $mockHomeModule = $this->createMock(Module::class);
        $mockHomeModule->method('toArray')->willReturn($moduleData);

        // Mock the module service which is used to load modules for the user
        $this->mockModuleService->method('getForUser')->willReturn([
            "home" => $mockHomeModule
        ]);

        // Make sure getGetAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->accountController->getGetAction($request);
        $this->assertEquals([
            "id" => $this->mockAccount->getAccountId(),
            "name" => null,
            "orgName" => "",
            "defaultModule" => "home",
            "modules" => [$moduleData]
        ], $response->getOutputBuffer());
    }

    /**
     * Test the getting the account billing
     */
    public function testGetAccountBillingAction()
    {
        $contactId = Uuid::uuid4()->toString();
        $contactDetails = [
            'obj_type' => ObjectTypes::CONTACT,
            'entity_id' => $contactId,
            'name' => 'Billing Payment Contact',
            'description' => 'Contact used for sales payment profile'
        ];

        // Create test contact entity
        $mockCustomerEntity = $this->createMock(CustomerEntity::class);
        $mockCustomerEntity->method('getName')->willReturn($contactDetails['name']);
        $mockCustomerEntity->method('getEntityId')->willReturn($contactId);
        $mockCustomerEntity->method('toArray')->willReturn($contactDetails);

        // Mock the account billing service which is used to get the contact for account
        $profileName = "Card ending in ....1111";
        $this->accountBillingService->method('getContactForAccount')->willReturn($mockCustomerEntity);
        $this->accountBillingService->method('getDefaultPaymentProfileName')->willReturn($profileName);
        $this->accountBillingService->method('getNumActiveUsers')->willReturn(1);

        // Make sure getGetAccountBillingAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->accountController->getGetAccountBillingAction($request);
        $this->assertEquals([
            "id" => $this->mockAccount->getAccountId(),            
            "name" => $this->mockAccount->getName(),
            "status" => $this->mockAccount->getStatus(),
            "status_name" => $this->mockAccount->getStatusName(),
            "contact_id" => $contactId,
            "payment_profile_name" => $profileName,
            "active_users" => 1,
            "per_user" => AccountBillingService::PRICE_PER_USER
        ], $response->getOutputBuffer());
    }

    /**
     * Test the updating of account contact
     */
    public function testUpdateAccountContactAction()
    {
        $contactId = Uuid::uuid4()->toString();

        // Make sure postUpdateAccountContactAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['contact_id' => $contactId]));
        $response = $this->accountController->postUpdateAccountContactAction($request);
        $this->assertEquals(true, $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in updating the account contact
     */
    public function testUpdateAccountContactActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->accountController->postUpdateAccountContactAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postUpdateAccountContactAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->accountController->postUpdateAccountContactAction($request);

        // It should return an error if no contact_id is provided in the params
        $this->assertEquals(['error' => 'contact_id is a required param.'], $response->getOutputBuffer());
    }

    /**
     * Test the updating account billing details
     */
    public function testUpdateBillingAction()
    {
        $contactId = Uuid::uuid4()->toString();

        // Mock the account billing service which is used to save the payment profile
        $profileName = "Card ending in ....1111";
        $this->accountBillingService->method('savePaymentProfile')->willReturn($profileName);

        // Make sure postUpdateAccountContactAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode([
            'contact_id' => $contactId,
            'number' => '4111111111111111',
            'ccv' => '762',
            'monthExpires' => '10',
            'yearExpires' => '2025'
        ]));
        $response = $this->accountController->postUpdateBillingAction($request);
        $this->assertEquals($profileName, $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in updating account billing details
     */
    public function testUpdateBillingActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->accountController->postUpdateBillingAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postUpdateBillingAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->accountController->postUpdateBillingAction($request);

        // It should return an error if no contact_id is provided in the params
        $this->assertEquals(['error' => 'contact_id is a required param.'], $response->getOutputBuffer());
    }
}
