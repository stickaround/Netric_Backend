<?php

namespace NetricTest\Application\Health\DependencyCheck;

use Netric\Application\Health\DependencyCheck\PgsqlDependencyCheck;
use Netric\Config\ConfigFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;

/**
 * Make sure we can test a connection to a database
 * 
 * @group integration
 */
class PgsqlDependencyCheckTest extends TestCase
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

        $dependency = new PgsqlDependencyCheck(
            $config->db->host,
            $config->db->user,
            $config->db->password
        );
        $this->assertTrue($dependency->isAvailable());
    }

    /**
     * Make sure that it fails if there is no database
     *
     * @return void
     */
    public function testIsNotActive()
    {
        $dependency = new PgsqlDependencyCheck(
            'noexist',
            'baduser',
            'badpass'
        );
        $this->assertFalse($dependency->isAvailable());
    }

    /**
     * Make sure we can get a description of any of the running params
     */
    public function testGetParamsDescription()
    {
        $dependency = new PgsqlDependencyCheck(
            "dbhost",
            "user",
            "password"
        );
        $this->assertNotNull($dependency->getParamsDescription());
    }
}
