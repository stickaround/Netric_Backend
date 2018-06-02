<?php
namespace NetricTest\Application\Health\DependencyCheck;

use Netric\Application\Health\DependencyCheck\DependenciesFactory;
use PHPUnit\Framework\TestCase;

/**
 * Make sure our dependencies factory works
 */
class DependenciesFactoryTest extends TestCase
{
    /**
     * Reference to account running for unit tests
     *
     * @var \Netric\Account\Account
     */
    private $account = null;


    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
    }

    public function testCreateService()
    {
        $sl = $this->account->getServiceManager();
        $this->assertGreaterThanOrEqual(
            0,
            $sl->get(DependenciesFactory::class)
        );
    }
}
