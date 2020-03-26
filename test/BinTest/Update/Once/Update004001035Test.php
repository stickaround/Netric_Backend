<?php
/**
 * Make sure the bin/scripts/update/once/004/001/035.php script works
 */
namespace BinTest\Update\Once;

use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\TaskEntity;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory as EntityDataMapperFactory;
use Netric\Log\LogFactory;
use Netric\Db\Relational\RelationalDbFactory;


class Update004001035Test extends TestCase
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
     * Entities to cleanup
     *
     * @var array
     */
    private $testEntities = array();

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/035.php";
        $this->entityDataMapper = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
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
        $entityDataMapper = $serviceManager->get(EntityDataMapperFactory::class);
        $entityLoader = $serviceManager->get(EntityLoaderFactory::class);
        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $rdb = $this->account->getServiceManager()->get(RelationalDbFactory::class);
        
        // Execute the script
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));
    }
}