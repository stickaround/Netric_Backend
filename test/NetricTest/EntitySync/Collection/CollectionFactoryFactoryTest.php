<?php

namespace NetricTest\EntitySync\Collection;

use Netric;
use Netric\EntitySync\Collection\CollectionFactory;
use Netric\EntitySync\Collection\CollectionFactoryFactory;
use NetricTest\Bootstrap;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class CollectionFactoryFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            CollectionFactory::class,
            $sm->get(CollectionFactoryFactory::class)
        );
    }
}
