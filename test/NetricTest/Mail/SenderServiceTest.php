<?php

namespace NetricTest\Mail;

use Aereus\Config\Config;
use Netric\Mail\SenderService;
use Netric\Account\Account;
use Netric\Entity\ObjType\EmailMessageEntity;
use PHPUnit\Framework\TestCase;
use Netric\Log\LogInterface;

/**
 * @group integration
 */
class SenderServiceTest extends TestCase
{
    /**
     * Sender service
     *
     * @var SenderService
     */
    private $senderService = null;

    /**
     * Active test account
     *
     * @var Account
     */
    private $account = null;

    protected function setUp(): void
    {
        $this->senderService = new SenderService(
            $this->createStub(LogInterface::class),
            new Config([
                'server' => 'smtp4dev',
                'port' => 25, 'noreply' =>
                'from@example.com'
            ])
        );
    }
    public function testSend()
    {
        $this->assertTrue(
            $this->senderService->send(
                'test@example.com',
                "Test To",
                "from@example.com",
                "From Name",
                'test',
                'body'
            )
        );
    }

    public function testSendEmailMessage()
    {
        $emailMessage = $this->createMock(EmailMessageEntity::class);
        $emailMessage->method('getValue')->will(
            $this->returnValueMap([
                ['to', 'test@example.com'],
                ['subject', 'Test'],
                ['body', 'Body'],
                ['body_type', EmailMessageEntity::BODY_TYPE_PLAIN],
                ['message_id', 'MEDSSAGE-ID'],
            ])
        );

        $emailMessage->method('getFromData')->willReturn(
            ['address' => 'from@example.com', 'display' => 'Test From']
        );

        $emailMessage->method('getReplyToData')->willReturn(
            ['address' => 'reply@example.com', 'display' => 'Test Reply']
        );

        $emailMessage->method('getToData')->willReturn(
            [['address' => 'test@example.com', 'display' => 'Test To']]
        );

        $emailMessage->method('getCcData')->willReturn(
            [['address' => 'testcc@example.com', 'display' => 'Test CC']]
        );

        $emailMessage->method('getBccData')->willReturn(
            [['address' => 'testbcc@example.com', 'display' => 'Test BCC']]
        );

        $this->assertTrue(
            $this->senderService->sendEmailMessage(
                $emailMessage
            )
        );
    }
}
