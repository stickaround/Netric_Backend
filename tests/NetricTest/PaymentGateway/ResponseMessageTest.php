<?php
namespace NetricTest\PaymentGateway;

use Netric\PaymentGateway\ResponseMessage;
use PHPUnit\Framework\TestCase;

/**
 * Validate common gateway message class
 */
class ResponseMessageTest extends TestCase
{
    public function testGetCode()
    {
        $message = new ResponseMessage('TESTCODE', 'mssg');
        $this->assertEquals('TESTCODE', $message->getCode());
    }

    public function testGetText()
    {
        $fullMessageText = 'The credit card you entered was not valid';
        $message = new ResponseMessage('TESTCODE', $fullMessageText);
        $this->assertEquals($fullMessageText, $message->getText());
    }
}