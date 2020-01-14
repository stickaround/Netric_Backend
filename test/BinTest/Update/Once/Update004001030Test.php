<?php
/**
 * Make sure the bin/scripts/update/once/004/001/030.php script works
 */
namespace BinTest\Update\Once;

use Netric\Db\Relational\RelationalDbFactory;
use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\LoaderFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\UserEntity;

class Update004001030Test extends TestCase
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
     * Group ids to cleanup
     *
     * @var array
     */
    private $testGroups = array();

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/030.php";
        $this->loader = $this->account->getServiceManager()->get(LoaderFactory::class);
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
    {
        // Get the groupings for this obj_type and field_name
        $groupings = $this->loader->get(ObjectTypes::ISSUE, "status_id");

        // Delete the added groups
        foreach ($this->testGroups as $group) {
            $groupings->delete($group->id);
        }

        // Save the changes in groupings
        $this->loader->save($groupings);
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
        $db = $this->account->getServiceManager()->get(RelationalDbFactory::class);

        // Get the groupings for this obj_type and field_name
        $groupings = $this->loader->get(ObjectTypes::ISSUE, "status_id");

        // Create a new instance of group with user id
        $currentUser = $this->account->getUser(UserEntity::USER_CURRENT);
        $groupWithUserId = new Group();
        $groupWithUserId->setValue("name", "UnitTestOnce 030 group with user guid");
        $groupWithUserId->setValue("user_id", $currentUser->getId());
        $groupings->add($groupWithUserId);

        // Save the changes in groupings
        $this->loader->save($groupings);
        $this->testGroups[] = $groupWithUserId;
      
        $result = $db->query("SELECT * FROM object_groupings WHERE id = {$groupWithUserId->id}");
        $row = $result->fetch();

        // Make sure that we have null guid and path
        $this->assertEquals($row["name"], "UnitTestOnce 030 group with user guid");
        $this->assertNull($row["guid"]);
        $this->assertNull($row["path"]);

        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Query again the group with user id and this time it should have a path and guid
        $result = $db->query("SELECT * FROM object_groupings WHERE id = {$groupWithUserId->id}");
        $row = $result->fetch();

        // Make sure that we have path value (object type / field name / user id)
        $this->assertEquals($row["path"], ObjectTypes::ISSUE . "/status_id/" . UserEntity::USER_CURRENT);
    }
}