<?php
/**
 * Test the SchemDataMapper service factory
 */
namespace NetricTest\Account\Schema;

use PHPUnit_Framework_TestCase;

class SchemaDataMapperFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Account\Schema\SchemaDataMapperInterface',
            $sm->get('Netric\Account\Schema\SchemaDataMapper')
        );
    }
}