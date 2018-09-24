<?php

namespace NetricTest\EntityDefinition;

use Netric\EntityDefinition\EntityDefinitionLoader;
use NetricTest\Bootstrap;

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
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();

        $this->assertInstanceOf(
            EntityDefinitionLoader::class,
            $sm->get(EntityDefinitionLoader::class)
        );
    }
}
