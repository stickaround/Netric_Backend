<?php

namespace NetricTest\FileSystem;

use PHPUnit\Framework\TestCase;
use Netric\Account\Module\DataMapper\DataMapperInterface;
use Netric\Account\Module\DataMapper\DataMapperFactory;
use NetricTest\Bootstrap;

/**
 * @group integration
 */
class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            DataMapperInterface::class,
            $sm->get(DataMapperFactory::class)
        );
    }
}
