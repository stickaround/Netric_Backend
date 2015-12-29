<?php
/**
 * Netric Framework (http://framework.Netric.com/)
 *
 * @link      http://github.com/Netricframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Netric Technologies USA Inc. (http://www.Netric.com)
 * @license   http://framework.Netric.com/license/new-bsd New BSD License
 */

namespace NetricTest\Mail;

use Netric\Mail\Address;
use Netric\Mail\AddressList;

/**
 * @group      Netric_Mail
 */
class AddressListTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->list = new AddressList();
    }

    public function testImplementsCountable()
    {
        $this->assertInstanceOf('Countable', $this->list);
    }

    public function testIsEmptyByDefault()
    {
        $this->assertEquals(0, count($this->list));
    }

    public function testAddingEmailsIncreasesCount()
    {
        $this->list->add('zf-devteam@Netric.com');
        $this->assertEquals(1, count($this->list));
    }

    public function testImplementsTraversable()
    {
        $this->assertInstanceOf('Traversable', $this->list);
    }

    public function testHasReturnsFalseWhenAddressNotInList()
    {
        $this->assertFalse($this->list->has('foo@example.com'));
    }

    public function testHasReturnsTrueWhenAddressInList()
    {
        $this->list->add('zf-devteam@Netric.com');
        $this->assertTrue($this->list->has('zf-devteam@Netric.com'));
    }

    public function testGetReturnsFalseWhenEmailNotFound()
    {
        $this->assertFalse($this->list->get('foo@example.com'));
    }

    public function testGetReturnsAddressObjectWhenEmailFound()
    {
        $this->list->add('zf-devteam@Netric.com');
        $address = $this->list->get('zf-devteam@Netric.com');
        $this->assertInstanceOf('Netric\Mail\Address', $address);
        $this->assertEquals('zf-devteam@Netric.com', $address->getEmail());
    }

    public function testCanAddAddressWithName()
    {
        $this->list->add('zf-devteam@Netric.com', 'ZF DevTeam');
        $address = $this->list->get('zf-devteam@Netric.com');
        $this->assertInstanceOf('Netric\Mail\Address', $address);
        $this->assertEquals('zf-devteam@Netric.com', $address->getEmail());
        $this->assertEquals('ZF DevTeam', $address->getName());
    }

    public function testCanAddManyAddressesAtOnce()
    {
        $addresses = [
            'zf-devteam@Netric.com',
            'zf-contributors@lists.Netric.com' => 'ZF Contributors List',
            new Address('fw-announce@lists.Netric.com', 'ZF Announce List'),
        ];
        $this->list->addMany($addresses);
        $this->assertEquals(3, count($this->list));
        $this->assertTrue($this->list->has('zf-devteam@Netric.com'));
        $this->assertTrue($this->list->has('zf-contributors@lists.Netric.com'));
        $this->assertTrue($this->list->has('fw-announce@lists.Netric.com'));
    }

    public function testDoesNotStoreDuplicatesAndFirstWins()
    {
        $addresses = [
            'zf-devteam@Netric.com',
            new Address('zf-devteam@Netric.com', 'ZF DevTeam'),
        ];
        $this->list->addMany($addresses);
        $this->assertEquals(1, count($this->list));
        $this->assertTrue($this->list->has('zf-devteam@Netric.com'));
        $address = $this->list->get('zf-devteam@Netric.com');
        $this->assertNull($address->getName());
    }
}
