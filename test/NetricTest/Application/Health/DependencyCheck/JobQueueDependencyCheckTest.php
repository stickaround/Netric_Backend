<?php

namespace NetricTest\Application\Health\DependencyCheck;

use JobQueueApiFactory\JobQueueApiFactory;
use Netric\Application\Health\DependencyCheck\JobQueueDependencyCheck;
use Netric\Application\Health\DependencyCheck\PgsqlDependencyCheck;
use Netric\Config\ConfigFactory;
use Netric\Log\LogFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

/**
 * Make sure we can test a connection to the jobqueue
 *
 * @group integration
 */
class JobQueueDependencyCheckTest extends TestCase
{
    /**
     * Make sure that we can connect to a database
     *
     * @return void
     */
    public function testIsActive()
    {
        $account = Bootstrap::getAccount();
        $serviceLocator = $account->getServiceManager();
        $config = $serviceLocator->get(ConfigFactory::class);
        $clientFactory = new JobQueueApiFactory();
        $client = $clientFactory->createJobQueueClient($config->workers->server);

        $dependency = new JobQueueDependencyCheck(
            $client,
            $serviceLocator->get(LogFactory::class)
        );
        $this->assertTrue($dependency->isAvailable());
    }
}
