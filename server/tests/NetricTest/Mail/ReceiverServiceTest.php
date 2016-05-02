<?php
namespace NetricTest\Mail;

use Netric\Mail\Transport\TransportInterface;
use Netric\Mail\Transport\InMemory;
use Netric\Mail\SenderService;
use Netric\Mail\Message;
use Netric\Account\Account;
use PHPUnit_Framework_TestCase;

class ReceiverServiceTest extends PHPUnit_Framework_TestCase
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

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->transport = new InMemory();
        $this->bulkTransport = new InMemory();
        $log = $this->account->getServiceManager()->get("Log");
        $this->senderService = new SenderService(
            $this->transport,
            $this->bulkTransport,
            $log
        );

    }
}