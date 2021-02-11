<?php

namespace NetricTest\Application\Health\DependencyCheck;

use Netric\Application\Health\DependencyCheck\MogileFsDependencyCheck;
use Netric\Config\ConfigFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

/**
 * Make sure we can test a connection to our file store
 *
 * @group integration
 */
class MogileFsDependencyCheckTest extends TestCase
{
    /**
     * Make sure that we can connect to mogilefs
     *
     * @return void
     */
    public function testIsActive()
    {
        $account = Bootstrap::getAccount();
        $serviceLocator = $account->getServiceManager();
        $config = $serviceLocator->get(ConfigFactory::class);

        $dependency = new MogileFsDependencyCheck(
            $config->files->mogileServer,
            $config->files->mogileAccount,
            $config->files->mogilePort
        );
        $this->assertTrue($dependency->isAvailable());
    }

    /**
     * Make sure we can get a description of any of the running params
     */
    public function testGetParamsDescription()
    {
        $dependency = new MogileFsDependencyCheck(
            "server",
            "account",
            1234 // port
        );
        $this->assertNotNull($dependency->getParamsDescription());
    }
}
