<?php

namespace NetricTest\Controller;

use Netric\Application\Response\HttpResponse;
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
     * Try sending a draft email
     */
    public function testPostSendAction()
    {
        // Create test email message
        $mockEmailMessage = $this->createMock(EmailMessageEntity::class);

        // Mock the entity loader service which is used to load the email_message by guid
        $entityLoader = $this->createMock(EntityLoader::class);
        $entityLoader->method('getByGuid')->willReturn($mockEmailMessage);

        // Create a mock sender service that is used to actually transport the message to SMTP
        $senderService = $this->createMock(SenderService::class);
        $senderService->method('send')->willReturn(true);

        // Create a mock delivery service that will handle receiving a message and saving it
        $deliveryService = $this->createMock(DeliveryService::class);

        // Create mock log
        $log = $this->createMock(LogInterface::class);

        // Create the controller with mocks
        $controller = new EmailController($entityLoader, $senderService, $deliveryService, $log);

        // Make sure send is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['entity_id' => Uuid::uuid4()->toString()]));
        $response = $controller->postSendAction($request);
        $this->assertEquals(['result' => true], $response->getOutputBuffer());
    }

    /**
     * Make sure call without body fails
     */
    public function testPostSendActionNoBody()
    {
        // Mocks for DI - they are never used though
        $entityLoader = $this->createMock(EntityLoader::class);
        $senderService = $this->createMock(SenderService::class);
        $deliveryService = $this->createMock(DeliveryService::class);
        $log = $this->createMock(LogInterface::class);

        // Create the controller with mocks
        $controller = new EmailController($entityLoader, $senderService, $deliveryService, $log);

        // Make sure send is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $controller->postSendAction($request);

        $this->assertEquals(HttpResponse::STATUS_CODE_BAD_REQUEST, $response->getReturnCode());
    }

    /**
     * Make sure call without sending it the guid of a saved message it fails
     */
    public function testPostSendActionNoSavedEmail()
    {
        // Mocks for DI - they are never used though
        $entityLoader = $this->createMock(EntityLoader::class);
        $senderService = $this->createMock(SenderService::class);
        $deliveryService = $this->createMock(DeliveryService::class);
        $log = $this->createMock(LogInterface::class);

        // Create the controller with mocks
        $controller = new EmailController($entityLoader, $senderService, $deliveryService, $log);

        // Make sure send is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $controller->postSendAction($request);

        $this->assertEquals(HttpResponse::STATUS_CODE_BAD_REQUEST, $response->getReturnCode());
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

        // Create mocks
        $entityLoader = $this->createMock(EntityLoader::class);
        $senderService = $this->createMock(SenderService::class);
        $log = $this->createMock(LogInterface::class);

        // Create a mock delivery service that will handle receiving a message and saving it
        $deliveryService = $this->createMock(DeliveryService::class);
        $deliveryService->method('deliverMessageFromFile')->willReturn($mockGuid);

        // Create the controller with mocks
        $controller = new EmailController($entityLoader, $senderService, $deliveryService, $log);
        $controller->testMode = true;

        // Make sure send is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setParam("files", ['message' => [
            "tmp_name" => __DIR__ . "/fixtures/mail.mime.txt", "name" => "files-upload-test.txt"
        ]]);
        $request->setParam('recipient', 'test@netric.com');

        $response = $controller->postReceiveAction($request);
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

        // Create the controller with mocks
        $controller = new EmailController($entityLoader, $senderService, $deliveryService, $log);

        // Create a request that is missing 'message' and 'recipient'
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);

        $response = $controller->postReceiveAction($request);
        $this->assertEquals(HttpResponse::STATUS_CODE_BAD_REQUEST, $response->getReturnCode());
    }
}
