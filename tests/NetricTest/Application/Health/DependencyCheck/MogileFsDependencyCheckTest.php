<?php
namespace NetricTest\Application\Health\DependencyCheck;

use Netric\Application\Health\DependencyCheck\MogileFsDependencyCheck;
use Netric\Config\ConfigFactory;
use PHPUnit\Framework\TestCase;

/**
 * Make sure we can test a connection to our file store
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
        $account = \NetricTest\Bootstrap::getAccount();
        $serviceLocator = $account->getServiceManager();
        $config = $serviceLocator->get(ConfigFactory::class);

        $dependency = new MogileFsDependencyCheck(
            $config->files->server,
            $config->files->account,
            $config->files->port
        );
        $this->assertTrue($dependency->isAvailable());
    }
}
