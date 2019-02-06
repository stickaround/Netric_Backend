<?php
namespace NetricTest\Controller;

use Netric\Entity\EntityLoader;
use Netric\Controller\EmailController;
use Netric\Entity\ObjType\EmailMessageEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\FileSystem\FileSystem;
use Netric\Mail\SenderService;
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

        // Create the controller with mocks
        $controller = new EmailController($entityLoader, $senderService);

        // Make sure send is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['guid'=>uniqid()]));
        $response = $controller->postSendAction($request);
        $this->assertEquals(['result'=>true], $response->getOutputBuffer());
    }
}
