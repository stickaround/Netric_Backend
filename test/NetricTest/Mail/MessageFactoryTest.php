<?php
/**
 * Netric Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Netric Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace NetricTest\Mail;

use Netric\Mail\MessageFactory;
use PHPUnit\Framework\TestCase;
use Netric\Mail\AddressList;
use Netric\Mail\Message;
use Netric\Mail\Address;
use Netric\Mail\Exception\InvalidArgumentException;

/**
 * @group      Netric_Mail
 */
class MessageFactoryTest extends TestCase
{
    public function testConstructMessageWithOptions()
    {
        $options = [
            'encoding'  => 'UTF-8',
            'from'      => 'matthew@example.com',
            'to'        => 'zf-devteam@example.com',
            'cc'        => 'zf-contributors@example.com',
            'bcc'       => 'zf-devteam@example.com',
            'reply-to'  => 'matthew@example.com',
            'sender'    => 'matthew@example.com',
            'subject'   => 'subject',
            'body'      => 'body',
        ];

        $message = MessageFactory::getInstance($options);

        $this->assertInstanceOf(Message::class, $message);
        $this->assertEquals('UTF-8', $message->getEncoding());
        $this->assertEquals('subject', $message->getSubject());
        $this->assertEquals('body', $message->getBody());
        $this->assertInstanceOf(Address::class, $message->getSender());
        $this->assertEquals($options['sender'], $message->getSender()->getEmail());

        $getMethods = [
            'from'      => 'getFrom',
            'to'        => 'getTo',
            'cc'        => 'getCc',
            'bcc'       => 'getBcc',
            'reply-to'  => 'getReplyTo',
        ];

        foreach ($getMethods as $key => $method) {
            $value = $message->{$method}();
            $this->assertInstanceOf(AddressList::class, $value);
            $this->assertEquals(1, count($value));
            $this->assertTrue($value->has($options[$key]));
        }
    }

    public function testCanCreateMessageWithMultipleRecipientsViaArrayValue()
    {
        $options = [
            'from' => ['matthew@example.com' => 'Matthew'],
            'to'   => [
                'zf-devteam@example.com',
                'zf-contributors@example.com',
            ],
        ];

        $message = MessageFactory::getInstance($options);

        $from = $message->getFrom();
        $this->assertInstanceOf(AddressList::class, $from);
        $this->assertEquals(1, count($from));
        $this->assertTrue($from->has('matthew@example.com'));
        $this->assertEquals('Matthew', $from->get('matthew@example.com')->getName());

        $to = $message->getTo();
        $this->assertInstanceOf(AddressList::class, $to);
        $this->assertEquals(2, count($to));
        $this->assertTrue($to->has('zf-devteam@example.com'));
        $this->assertTrue($to->has('zf-contributors@example.com'));
    }

    public function testIgnoresUnreconizedOptions()
    {
        $options = [
            'foo' => 'bar',
        ];
        $mail = MessageFactory::getInstance($options);
        $this->assertInstanceOf(Message::class, $mail);
    }

    public function testEmptyOption()
    {
        $options = [];
        $mail = MessageFactory::getInstance();
        $this->assertInstanceOf(Message::class, $mail);
    }

    public function invalidMessageOptions()
    {
        return [
            'null' => [null],
            'bool' => [true],
            'int' => [1],
            'float' => [1.1],
            'string' => ['not-an-array'],
            'plain-object' => [(object) [
                'from' => 'matthew@example.com',
                'to'   => 'foo@example.com',
            ]],
        ];
    }

    /**
     * @dataProvider invalidMessageOptions
     */
    public function testExceptionForOptionsNotArrayOrTraversable($options)
    {
        $this->expectException(InvalidArgumentException::class);
        MessageFactory::getInstance($options);
    }
}
