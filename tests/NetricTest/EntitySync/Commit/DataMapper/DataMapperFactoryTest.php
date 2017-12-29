<?php

namespace NetricTest\EntitySync\Commit\DataMapper;

use Netric;

use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            'Netric\EntitySync\Commit\DataMapper\Pgsql',
            $sm->get('EntitySyncCommit_DataMapper')
        );

        $this->assertInstanceOf(
            'Netric\EntitySync\Commit\DataMapper\PgSql',
            $sm->get('Netric\EntitySync\Commit\DataMapper\DataMapper')
        );
    }
}