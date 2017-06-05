<?php

namespace NetricTest\EntityQuery\Index;

use Netric;

use PHPUnit\Framework\TestCase;

class IndexFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\EntityQuery\Index\Pgsql',
            $sm->get('EntityQuery_Index')
        );

        $this->assertInstanceOf(
            'Netric\EntityQuery\Index\Pgsql',
            $sm->get('Netric\EntityQuery\Index\Index')
        );
    }
}