<?php

namespace NetricTest\EntityQuery\Index;

use Netric\EntityQuery\Index\EntityQueryIndexRdb;
use NetricTest\Bootstrap;
use Netric\EntityQuery\Index\IndexFactory;
use PHPUnit\Framework\TestCase;

class IndexFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityQueryIndexRdb::class,
            $sm->get(IndexFactory::class)
        );
    }
}
