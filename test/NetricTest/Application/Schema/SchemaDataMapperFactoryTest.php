<?php
/**
 * Test the SchemDataMapper service factory
 */
namespace NetricTest\Application\Schema;

use PHPUnit\Framework\TestCase;
use Netric\Application\Schema\SchemaDataMapperInterface;
use Netric\Application\Schema\SchemaDataMapperFactory;

class SchemaDataMapperFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            SchemaDataMapperInterface::class,
            $sm->get(SchemaDataMapperFactory::class)
        );
    }
}
