<?php

namespace NetricTest\Permissions;

use Netric;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Permissions\DaclLoader;
use Netric\Permissions\DaclLoaderFactory;

/**
 * @group integration
 */
class DaclLoaderactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $sm = $account->getServiceManager();
        $this->assertInstanceOf(
            DaclLoader::class,
            $sm->get(DaclLoaderFactory::class)
        );
    }
}
