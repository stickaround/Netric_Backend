<?php
namespace NetricTest\FileSystem;

use Netric\Application\Health\HealthCheck;
use Netric\Application\Health\HealthCheckFactory;

use PHPUnit\Framework\TestCase;

/**
 * Make sure we can construct a HealthCheck with all the system dependencies
 */
class HealthCheckFactoryTest extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;


    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    public function testCreateService()
    {
        $sl = $this->account->getServiceManager();
        $this->assertInstanceOf(
            HealthCheck::class,
            $sl->get(HealthCheckFactory::class)
        );
    }
}
