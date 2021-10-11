<?php

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationIdentity;
use Netric\Authentication\AuthenticationService;
use Netric\Entity\EntityLoader;
use Netric\Controller\EmailController;
use Netric\Entity\ObjType\EmailMessageEntity;
use Netric\Log\LogInterface;
use Netric\Mail\SenderService;
use Netric\Mail\DeliveryService;
use Netric\Request\HttpRequest;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * Test calling the email controller
 */
class EmailControllerTest extends TestCase
{
    /**
     * Dependency mocks
     */
    private EntityLoader $mockEntityLoader;
    private AuthenticationService $mockAuthService;
    private LogInterface $mockLog;
    private SenderService $mockSenderService;
    private DeliveryService $mockDeliveryService;

    /**
     * Initialized controller with mock dependencies
     */
    private EmailController $emailController;

    protected function setUp(): void
    {
        // Create mocks
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockSenderService = $this->createMock(SenderService::class);
        $this->mockLog = $this->createMock(LogInterface::class);

        $this->mockDeliveryService = $this->createMock(DeliveryService::class);

        // Provide identity for mock auth service
        $this->mockAuthService = $this->createMock(AuthenticationService::class);
        $ident = new AuthenticationIdentity('blahaccount', 'blahuser');
        $this->mockAuthService->method('getIdentity')->willReturn($ident);

        // Return mock authenticated account
        $mockAccount = $this->createStub(Account::class);
        $accountContainer = $this->createMock(AccountContainerInterface::class);
        $accountContainer->method('loadById')->willReturn($mockAccount);

        // Create the controller with mocks
        $this->emailController = new EmailController(
            $this->mockDeliveryService,
            $this->mockLog,
            $this->mockAuthService,
            $accountContainer
        );
        $this->emailController->testMode = true;
    }


    /**
     * Make sure we can receive a new message
     *
     * @throws \ReflectionException
     */
    public function testPostReceiveAction()
    {
        // Crate mock guid for a new message
        $mockGuid = uniqid();

        // Return a mock of the ID of the message delivered
        $this->mockDeliveryService->method('deliverMessageFromFile')->willReturn($mockGuid);

        // Make sure send is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setParam("files", ['message' => [
            "tmp_name" => __DIR__ . "/fixtures/mail.mime.txt", "name" => "files-upload-test.txt"
        ]]);
        $request->setParam('recipient', 'test@netric.com');

        $response = $this->emailController->postReceiveAction($request);
        $this->assertEquals(['result' => true, 'entity_id' => $mockGuid], $response->getOutputBuffer());
    }

    /**
     * Assure action fails gracefully when the request is bad
     *
     * @throws \ReflectionException
     */
    public function testPostReceiveActionMissingDataFails()
    {
        // Create mocks
        $entityLoader = $this->createMock(EntityLoader::class);
        $senderService = $this->createMock(SenderService::class);
        $log = $this->createMock(LogInterface::class);
        $deliveryService = $this->createMock(DeliveryService::class);
        $accountContainer = $this->createMock(AccountContainerInterface::class);
        $authServiceMock = $this->createMock(AuthenticationService::class);

        // Return mock authenticated account
        $mockAccount = $this->createStub(Account::class);
        $accountContainer = $this->createMock(AccountContainerInterface::class);
        $accountContainer->method('loadById')->willReturn($mockAccount);

        // Create the controller with mocks
        $controller = new EmailController($deliveryService, $log, $authServiceMock, $accountContainer);

        // Create a request that is missing 'message' and 'recipient'
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);

        $response = $controller->postReceiveAction($request);
        $this->assertEquals(HttpResponse::STATUS_CODE_BAD_REQUEST, $response->getReturnCode());
    }
}
