<?php
/**
 * Make sure the bin/scripts/update/once/004/001/038.php script works
 */
namespace BinTest\Update\Once;

use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\WorkFlow\DataMapper\DataMapperFactory;
use Netric\WorkFlow\WorkFlowFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Where;
use Ramsey\Uuid\Uuid;

class Update004001038Test extends TestCase
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
     * Workflows to cleanup
     *
     * @var array
     */
    private $testWorkflows = array();

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/038.php";
        $this->workFlowDataMapper = $this->account->getServiceManager()->get(DataMapperFactory::class);
    }

    protected function tearDown(): void
{
        foreach ($this->testWorkflows as $workflow) {
            $this->workFlowDataMapper->delete($workflow);
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
        // Create a new workflow with a condition using "user_id" field name
        $workFlow = $this->account->getServiceManager()->get(WorkFlowFactory::class);
        $workFlow->setObjType(ObjectTypes::TASK);
        $workFlow->setOnlyOnConditionsUnmet(true);
        $workFlow->setOnUpdate(true);

        // Set the condition's field name to user_id
        $uuid = Uuid::uuid4()->toString();
        $condition = new Where("user_id");
        $condition->equals($uuid);
        $workFlow->addCondition($condition);

        $workFlowId = $this->workFlowDataMapper->save($workFlow);
        $this->testWorkflows[] = $workFlow;

        // Execute the script
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // WorkFlow condition should now be owner_id instead of user_id
        $updatedWorkFlow = $this->workFlowDataMapper->getById($workFlowId);
        $conditions = $updatedWorkFlow->getConditions();

        // Check if the field name was renamed from user_id to owner_id
        $this->assertEquals($conditions[0]->fieldName, "owner_id");

        // Make sure that have updated existing condition and did not add a new one
        $this->assertEquals(count($conditions), 1);
        $this->assertEquals($conditions[0]->value, $uuid);
    }
}