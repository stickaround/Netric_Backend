<?php

declare(strict_types=1);

namespace NetricTest\Controller;

use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationIdentity;
use Netric\Authentication\AuthenticationService;
use Netric\Entity\EntityLoader;
use Netric\Controller\TestmailController;
use Netric\Entity\ObjType\EmailMessageEntity;
use Netric\Mail\SenderService;
use Netric\Mail\DeliveryService;
use Netric\Request\HttpRequest;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * Test to send email
 */
class TestmailControllerTest extends TestCase
{
    private EntityLoader $mockEntityLoader;
    private AuthenticationService $mockAuthService;
    private SenderService $mockSenderService;
    private DeliveryService $mockDeliveryService;

    /**
     * Setup by creating some mocks
     */
    protected function setUp(): void
    {
        // Create mocks
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockSenderService = $this->createMock(SenderService::class);

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
        $this->emailController = new TestmailController(
            $this->mockEntityLoader,
            $this->mockSenderService,
            $this->mockDeliveryService,
            $this->mockAuthService,
            $accountContainer
        );
        $this->emailController->testMode = true;
    }

    /**
     * Test to send a draft email
     */
    public function testPostSendAction()
    {
        // Create test email message
        $mockEmailMessage = $this->createMock(EmailMessageEntity::class);

        // Mock the entity loader service which is used to load the email_message by guid
        $this->mockEntityLoader->method('getEntityById')->willReturn($mockEmailMessage);

        // Create a mock sender service that is used to actually transport the message to SMTP
        $this->mockSenderService->method('send')->willReturn(true);

        // Make sure send is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['entity_id' => Uuid::uuid4()->toString()]));
        $response = $this->emailController->postSendAction($request);
        $this->assertEquals(['result' => true], $response->getOutputBuffer());
    }
}
