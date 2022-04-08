<?php

declare(strict_types=1);

namespace NetricTest\Workflow\ActionExecutor;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\ObjType\EmailMessageEntity;
use Netric\Entity\ObjType\WorkflowActionEntity;
use Netric\EntityDefinition\EntityDefinition;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Where;
use Netric\Workflow\ActionExecutor\ActionExecutorInterface;
use Netric\Workflow\ActionExecutor\SendEmailActionExecutor;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\EntityQuery\Results;
use Netric\Mail\SenderService;

/**
 * Test action executor
 */
class SendEmailActionExecutorTest extends TestCase
{
    /**
     * Executor to test (not a mock of course)
     */
    private ActionExecutorInterface $executor;

    /**
     * mock dependencies
     */
    private EntityLoader $mockEntityLoader;
    private WorkflowActionEntity $mockActionEntity;
    private SenderService $mockSenderService;
    /**
     * Mock and stub out the action exector
     */
    protected function setUp(): void
    {
        $this->mockActionEntity = $this->createMock(WorkflowActionEntity::class);
        $this->mockEntityLoader = $this->createMock(EntityLoader::class);
        $this->mockSenderService = $this->createMock(SenderService::class);
        $this->executor = new SendEmailActionExecutor(
            $this->mockEntityLoader,
            $this->mockActionEntity,
            'http://mockhost',
            $this->mockSenderService
        );
    }

    /**
     * Make sure we can update a basic field
     */
    public function testExecute(): void
    {
        // Set the entity action data
        $this->mockActionEntity->method("getData")->willReturn([
            'subject' => 'Test',
            'from' => 'from@example.com',
            'to' => '<%email%>', // Check the email field of the user entity
            'cc' => 'test2@example.com',
            'bcc' => 'test4@example.com',
            'body' => 'Body',
        ]);

        // Create the test email message
        $mockEmailMessage = $this->createMock(EmailMessageEntity::class);
        $this->mockEntityLoader->method("create")->with(
            $this->equalTo(ObjectTypes::EMAIL_MESSAGE)
        )->willReturn($mockEmailMessage);

        // Make sure that setValue on the email message is corract
        $mockEmailMessage->expects($this->exactly(6))
            ->method('setValue')
            ->withConsecutive(
                [$this->equalTo('to'), $this->equalTo("user@example.com")],
                [$this->equalTo('from'), $this->equalTo("from@example.com")],
                [$this->equalTo('body'), $this->equalTo("Body")],
                [$this->equalTo('subject'), $this->equalTo("Test")],
                [$this->equalTo('cc'), $this->equalTo("test2@example.com")],
                [$this->equalTo('bcc'), $this->equalTo("test4@example.com")],
            );

        // Create test entity and a mock entity definition that is returned
        $testEntity = $this->createMock(EntityInterface::class);
        $testEntity->method('getEntityId')->willReturn('UUID-SAVED');
        $testEntity
            ->method('getValue')
            ->with(
                $this->equalTo('email'),
            )
            ->willReturn("user@example.com");
        $mockEntityDefinition = $this->createStub(EntityDefinition::class);
        $mockEntityDefinition->method('getObjType')->willReturn(ObjectTypes::USER);
        $testEntity->method("getDefinition")->willReturn($mockEntityDefinition);

        // Fake results
        $this->mockSenderService->method('sendEmailMessage')->willReturn(true);

        // Stub the user to satisfy requirements for call to execute
        $user = $this->createMock(UserEntity::class);
        $user->method('getAccountId')->willReturn('UUID-ACCOUNT-ID');

        // Execute
        $this->assertTrue($this->executor->execute($testEntity, $user));
    }

    /**
     * Make sure we can update a basic field
     */
    public function testExecuteWithTemplate(): void
    {
        // Set the entity action data
        $this->mockActionEntity->method("getData")->willReturn([
            'template_id' => "UUID_TEMPLATE",
            'from' => 'from@example.com',
            'to' => 'user@example.com', // Check the email field of the user entity
            'cc' => 'test2@example.com',
            'bcc' => 'test4@example.com',
        ]);
        $this->mockActionEntity->method('getAccountId')->willReturn('UUID-ACCOUNT-ID');

        // Create a test email template with mock body and html
        $emailTemplate = $this->createMock(EntityInterface::class);
        $emailTemplate->method('getValue')
            ->will($this->returnValueMap([
                ['body_html', '<body>HI</body>'],
                ['subject', 'Subject']
            ]));

        $this->mockEntityLoader->method("getEntityById")->with(
            $this->equalTo("UUID_TEMPLATE"),
            $this->equalTo('UUID-ACCOUNT-ID')
        )->willReturn($emailTemplate);

        // Create the test email message observer - this will normally just create a new email
        $mockEmailMessage = $this->createMock(EmailMessageEntity::class);
        $this->mockEntityLoader->method("create")->with(
            $this->equalTo(ObjectTypes::EMAIL_MESSAGE)
        )->willReturn($mockEmailMessage);

        // Make sure that setValue on the email message is corract
        $mockEmailMessage->expects($this->exactly(7))
            ->method('setValue')
            ->withConsecutive(
                [$this->equalTo('to'), $this->equalTo("user@example.com")],
                [$this->equalTo('from'), $this->equalTo("from@example.com")],
                [$this->equalTo('body'), $this->equalTo("<body>HI</body>")],
                [$this->equalTo('subject'), $this->equalTo("Subject")],
                [$this->equalTo('cc'), $this->equalTo("test2@example.com")],
                [$this->equalTo('bcc'), $this->equalTo("test4@example.com")],
                [$this->equalTo('body_type'), $this->equalTo(EmailMessageEntity::BODY_TYPE_HTML)],
            );

        // Create test entity and a mock entity definition that is returned
        $testEntity = $this->createMock(EntityInterface::class);
        $mockEntityDefinition = $this->createStub(EntityDefinition::class);
        $mockEntityDefinition->method('getObjType')->willReturn(ObjectTypes::USER);
        $testEntity->method("getDefinition")->willReturn($mockEntityDefinition);

        // Fake results
        $this->mockSenderService->method('sendEmailMessage')->willReturn(true);

        // Stub the user to satisfy requirements for call to execute
        $user = $this->createMock(UserEntity::class);


        // Execute
        $this->assertTrue($this->executor->execute($testEntity, $user));
    }
}
