<?php
namespace NetricTest\PaymentGateway;

use Netric\PaymentGateway\ChargeResponse;
use Netric\PaymentGateway\ResponseMessage;
use PHPUnit\Framework\TestCase;

/**
 * Test the ChargeResponse class
 */
class ChargeResponseTest extends TestCase
{
    public function testSetAndGetTransactionId()
    {
        $response = new ChargeResponse();
        $testId = '1234';
        $response->setTransactionId($testId);
        $this->assertEquals($testId, $response->getTransactionId());
    }

    public function testAddMessage()
    {
        $response = new ChargeResponse();
        $message = new ResponseMessage('code', 'message');
        $response->addMessage($message);
        $this->assertEquals(1, count($response->getMessages()));
    }

    public function testGetMessages()
    {
        $response = new ChargeResponse();
        $message = new ResponseMessage('code', 'message');
        $response->addMessage($message);
        $messages = $response->getMessages();
        $this->assertEquals('code', $messages[0]->getCode());
        $this->assertEquals('message', $messages[0]->getDescription());
    }

    public function testGetMessagesText()
    {
        $response = new ChargeResponse();
        $message = new ResponseMessage('code', 'message');
        $response->addMessage($message);
        $messagesText = $response->getMessagesText();
        $this->assertStringContainsString('code:', $messagesText);
        $this->assertStringContainsString('message', $messagesText);
    }

    public function testSetAndGetStatus()
    {
        $response = new ChargeResponse();
        $response->setStatus(ChargeResponse::STATUS_ERROR);
        $this->assertEquals(ChargeResponse::STATUS_ERROR, $response->getStatus());
    }
}
