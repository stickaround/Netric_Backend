<?php
namespace NetricTest\Mail;

use Netric\Mail\Transport\TransportInterface;
use Netric\Mail\Transport\InMemory;
use Netric\Mail\SenderService;
use Netric\Mail\Message;
use Netric\Account\Account;
use PHPUnit\Framework\TestCase;
use Netric\Log\LogFactory;
use Netric\Entity\EntityLoaderFactory;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;

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

    protected function setUp()
    {
        $this->account = Bootstrap::getAccount();
        $this->transport = new InMemory();
        $this->bulkTransport = new InMemory();
        $log = $this->account->getServiceManager()->get(LogFactory::class);
        $this->senderService = new SenderService(
            $this->transport,
            $this->bulkTransport,
            $log
        );
    }

    private function getMessage()
    {
        $message = new Message();
        $message->addTo('devteam@netric.com', 'Netric DevTeam')
            ->addCc('matthew@netric.com')
            ->addBcc('team@lists.netric.com', 'Project')
            ->addFrom([
                'devteam@netric.com',
                'matthew@zend.com' => 'Matthew',
            ])
            ->setSender('test@netric.com', 'Test User')
            ->setSubject('Testing Netric\Mail\Transport\Sendmail')
            ->setBody('This is only a test.');
        return $message;
    }

    public function testSend()
    {
        $message = $this->getMessage();

        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $emailMessage = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE);
        $emailMessage->fromMailMessage($message);
        $this->senderService->send($emailMessage);
        $this->assertEquals($message->getTo(), $this->transport->getLastMessage()->getTo());
    }

    public function testSendBulk()
    {
        $message = $this->getMessage();

        $entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $emailMessage = $entityLoader->create(ObjectTypes::EMAIL_MESSAGE);
        $emailMessage->fromMailMessage($message);
        $this->senderService->sendBulk($emailMessage);
        $this->assertEquals($message->getTo(), $this->bulkTransport->getLastMessage()->getTo());
    }
}
