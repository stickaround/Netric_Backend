<?php
/**
 * Test the EntityFactory service factory
 */
namespace NetricTest\Entity;

use Netric;
use PHPUnit\Framework\TestCase;

class EntityFactoryFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf('Netric\Entity\EntityFactory', $sm->get('EntityFactory'));
    }
}