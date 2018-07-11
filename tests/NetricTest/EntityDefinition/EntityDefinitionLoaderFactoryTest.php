<?php

namespace NetricTest\EntityDefinition;

use Netric\EntityDefinition\EntityDefinitionLoader;

use PHPUnit\Framework\TestCase;

/**
 * Make sure the entity definition loader works
 *
 * @group integration
 */
class EntityDefinitionLoaderFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityDefinitionLoader::class,
            $sm->get(EntityDefinitionLoader::class)
        );
    }
}
