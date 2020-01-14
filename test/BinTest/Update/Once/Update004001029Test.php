<?php
/**
 * Make sure the bin/scripts/update/once/004/001/029.php script works
 */
namespace BinTest\Update\Once;

use Netric\Db\Relational\RelationalDbFactory;
use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\Group;
use Netric\EntityGroupings\GroupingLoaderFactory;

class Update004001029Test extends TestCase
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
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/029.php";
        $this->groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
    {
        // Get the groupings for this obj_type and field_name
        $groupings = $this->groupingLoader->get(ObjectTypes::ISSUE, "status_id");

        // Delete the added groups
        foreach ($this->testGroups as $group) {
            $groupings->delete($group->id);
        }

        // Save the changes in groupings
        $this->groupingLoader->save($groupings);
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
        $groupings = $this->groupingLoader->get(ObjectTypes::ISSUE, "status_id");

        // Create a new instance of group and add it in the groupings
        $group = new Group();
        $group->setValue("name", "UnitTestOnce 029 group");
        $groupings->add($group);

        // Create a new instance of group with user id
        $groupWithUserId = new Group();
        $groupWithUserId->setValue("name", "UnitTestOnce 029 group with user id");
        $groupingsWithUserId = $this->groupingLoader->get(ObjectTypes::ISSUE, "status_id", 123);
        $groupingsWithUserId->add($groupWithUserId);

        // Save the changes in groupings
        $this->groupingLoader->save($groupings);
        $this->groupingLoader->save($groupingsWithUserId);
        $this->testGroups[] = $group;
        $this->testGroups[] = $groupWithUserId;
      
        $result = $db->query("SELECT * FROM object_groupings WHERE id = {$group->id}");
        $row = $result->fetch();
        $this->assertEquals($row["name"], "UnitTestOnce 029 group");

        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // Query again the group and this time it should have a path and guid
        $result = $db->query("SELECT * FROM object_groupings WHERE id = {$group->id}");
        $row = $result->fetch();

        // Make sure that we have null guid and path (object type / field name)
        $this->assertNotNull($row["guid"]);
        $this->assertEquals($row["path"], ObjectTypes::ISSUE . "/status_id");

        // Query again the group with user id and this time it should have a path and guid
        $result = $db->query("SELECT * FROM object_groupings WHERE id = {$groupWithUserId->id}");
        $row = $result->fetch();

        // Make sure that we have path value (object type / field name / user id)
        $this->assertEquals($row["path"], ObjectTypes::ISSUE . "/status_id/123");
    }
}