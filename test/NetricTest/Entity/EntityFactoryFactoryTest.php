<?php
/**
 * Test the EntityFactory service factory
 */
namespace NetricTest\Entity;

use Netric;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\EntityFactory;
use Netric\Entity\EntityFactoryFactory;

class EntityFactoryFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(EntityFactory::class, $sm->get(EntityFactoryFactory::class));
    }
}
