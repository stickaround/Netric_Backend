<?php

namespace NetricTest\Mail;

use Aereus\Config\Config;
use Netric\Mail\Transport\TransportInterface;
use Netric\Mail\SenderService;
use Netric\Account\Account;
use PHPUnit\Framework\TestCase;
use Netric\Log\LogInterface;

/**
 * @group integration
 */
class SenderServiceTest extends TestCase
{
    /**
     * In-Memory transport for testing
     *
     * @var TransportInterface
     */
    private $transport = null;

    /**
     * In-Memory transport for testing bulk messages
     *
     * @var TransportInterface
     */
    private $bulkTransport = null;

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
}
