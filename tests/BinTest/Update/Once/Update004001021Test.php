<?php
/**
 * Make sure the bin/scripts/update/once/004/001/021.php script works
 */
namespace BinTest\Update\Once;

use Netric\Entity\EntityLoaderFactory;
use Netric\Permissions\DaclLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Permissions\Dacl;
use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;

class Update004001021Test extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Path to the script to test
     *
     * @var string
     */
    private $scriptPath = null;

    /**
     * Test entities that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testEntities = [];

    /**
     * Test old entities that should be cleaned up on tearDown
     *
     * @var EntityInterface[]
     */
    private $testOldEntities = [];

    /**
     * Setup each test
     */
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/021.php";
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown()
    {
        // Cleanup any test entities
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        foreach ($this->testEntities as $entity) {
            $loader->delete($entity, true);
        }
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right, but why
     * not just use unit tests for our tests? :)
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }

    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testRun()
    {
        $serviceManager = $this->account->getServiceManager();
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $daclLoader = $serviceManager->get(DaclLoaderFactory::class);

        $userEntity = $entityLoader->create("user");
        $userEntity->setValue("name", "UnitTestUser1");
        $entityLoader->save($userEntity);
        $this->testEntities[] = $userEntity;

        // This script will update all the users to give view/edit permission to the owner of the user
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        $uEntity = $entityLoader->get("user", $userEntity->getId());
        $dacl = $daclLoader->getForEntity($uEntity);

        $this->assertTrue($dacl->isAllowed($uEntity, DACL::PERM_VIEW));
        $this->assertTrue($dacl->isAllowed($uEntity, DACL::PERM_EDIT));
    }
}