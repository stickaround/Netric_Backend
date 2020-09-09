<?php

namespace NetricTest\EntitySync\Collection;

use Netric;
use Netric\EntitySync\Collection\CollectionRdbDataMapper;
use Netric\EntitySync\Collection\CollectionDataMapperFactory;
use NetricTest\Bootstrap;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class CollectionDataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            CollectionRdbDataMapper::class,
            $sm->get(CollectionDataMapperFactory::class)
        );
    }
}
