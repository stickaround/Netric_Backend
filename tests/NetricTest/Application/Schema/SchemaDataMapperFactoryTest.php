<?php
/**
 * Test the SchemDataMapper service factory
 */
namespace NetricTest\Application\Schema;

use PHPUnit\Framework\TestCase;

class SchemaDataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            'Netric\Application\Schema\SchemaDataMapperInterface',
            $sm->get('Netric\Application\Schema\SchemaDataMapper')
        );
    }
}
