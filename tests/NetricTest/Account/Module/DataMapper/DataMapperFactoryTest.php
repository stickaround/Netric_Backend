<?php
/**
 * Test the FileSystem service factory
 */
namespace NetricTest\FileSystem;

use PHPUnit\Framework\TestCase;

class DataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Account\Module\DataMapper\DataMapperInterface',
            $sm->get('Netric\Account\Module\DataMapper\DataMapper')
        );
    }
}