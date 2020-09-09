<?php

namespace NetricTest\EntitySync\Collection;

use Netric;
use Netric\EntitySync\Collection\EntityCollection;
use Netric\EntitySync\Collection\EntityCollectionFactory;
use NetricTest\Bootstrap;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class EntityCollectionFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityCollection::class,
            $sm->get(EntityCollectionFactory::class)
        );
    }
}
