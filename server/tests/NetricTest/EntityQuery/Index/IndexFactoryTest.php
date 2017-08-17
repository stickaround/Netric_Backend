<?php

namespace NetricTest\EntityQuery\Index;

use Netric\EntityQuery\Index\Pgsql;

use PHPUnit\Framework\TestCase;

class IndexFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            Pgsql::class,
            $sm->get('EntityQuery_Index')
        );

        $this->assertInstanceOf(
            Pgsql::class,
            $sm->get('Netric\EntityQuery\Index\Index')
        );
    }
}