<?php

namespace NetricTest\EntitySync\Collection;

use Netric;
use Netric\EntitySync\Collection\GroupingCollection;
use Netric\EntitySync\Collection\GroupingCollectionFactory;
use NetricTest\Bootstrap;

use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class GroupingCollectionFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            GroupingCollection::class,
            $sm->get(GroupingCollectionFactory::class)
        );
    }
}
