<?php
namespace BinTest\Update\Once;

use Netric\Entity\ObjType\UserEntity;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Console\BinScript;
use Netric\Entity\EntityLoaderFactory;
use PHPUnit\Framework\TestCase;

/**
 * Make sure the bin/scripts/update/once/004/001/026.php script works
 */
class Update004001026Test extends TestCase
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
     * Test entity to make sure we update
     *
     * @var EntityInterface
     */
    private $testEntity = null;


    /**
     * Setup each test
     */
    protected function setUp(): void
{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/026.php";
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
        $db = $serviceManager->get(RelationalDbFactory::class);

        // Change the guid of the system user to some random UUID
        $db->update(
            'objects_user',
            ['guid'=>'bf82ad1b-2c39-4751-8890-a9f06579f840', 'uname'=>'system-notvalid'],
            ['name'=>'system']
        );

        // Run the 026.php update once script to scan the objects_moved table and update the referenced entities
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Make sure we can load the system user by the correct GUID
        $systemUser = $entityLoader->getByGuid(UserEntity::USER_SYSTEM);
        $this->assertNotNull($systemUser);
        $this->assertEquals('system', $systemUser->getValue('uname'));
    }
}
