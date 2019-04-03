<?php
namespace NetricTest\WorkFlow\Action;

use Netric\EntityGroupings\Group;
use Netric\WorkFlow\Action\ActionInterface;
use Netric\WorkFlow\WorkFlowInstance;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\LoaderFactory;

class AssignActionTest extends AbstractActionTests
{
    /**
     * Test entities to delete
     *
     * @var EntityInterface[]
     */
    private $testEntities = array();

    /**
     * Test WorkFlows to cleanup
     *
     * @var WorkFlow[]
     */
    private $testWorkFlows = array();

    /**
     * Cleanup entities
     */
    protected function tearDown(): void
{
        foreach ($this->testEntities as $entity) {
            $this->entityLoader->delete($entity, true);
        }

        foreach ($this->testWorkFlows as $workFlow) {
            $this->workFlowDataMapper->delete($workFlow);
        }

        parent::tearDown();
    }

    /**
     * All action tests must construct the action
     *
     * @return ActionInterface
     */
    protected function getAction()
    {
        return $this->actionFactory->create("assign");
    }

    /**
     * Test execution with a manual users list
     */
    public function testExecute()
    {
        // Create three users for assignment
        $user1 = $this->entityLoader->create(ObjectTypes::USER);
        $user1->setValue("name", "testuser-" . rand());
        $user1Id = $this->entityLoader->save($user1);
        $this->testEntities[] = $user1;

        $user2 = $this->entityLoader->create(ObjectTypes::USER);
        $user2->setValue("name", "testuser-" . rand());
        $user2Id = $this->entityLoader->save($user2);
        $this->testEntities[] = $user2;

        $user3 = $this->entityLoader->create(ObjectTypes::USER);
        $user3->setValue("name", "testuser-" . rand());
        $user3Id = $this->entityLoader->save($user3);
        $this->testEntities[] = $user3;

        $usersArray = [$user1Id, $user2Id, $user3Id];

        // Create new action and set values for the userlist
        $action = $this->getAction();
        $action->setParam('field', 'user_id');
        $action->setParam('users', implode(',', $usersArray));

        // Create a test task
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "test");
        $this->entityLoader->save($task);
        $this->testEntities[] = $task;

        // Create a fake WorkFlowInstance since the action does not a saved workflow or instance
        $workFlowInstance = new WorkFlowInstance(123, $task);

        // Now execute the action and make sure it updated the field in $task
        $this->assertTrue($action->execute($workFlowInstance));

        // Make sure the user was assigned to one of the users
        $this->assertTrue(in_array($task->getValue("user_id"), $usersArray));

        // Execute repeatedly and check the probability distribution
        $hits = [$user1Id => 0, $user2Id => 0, $user3Id => 0];
        for ($i = 0; $i < 100; $i++) {
            $action->execute($workFlowInstance);
            $hits[$task->getValue('user_id')]++;
        }

        // Make sure probabilities are in acceptable ranges ~20% to each
        $this->assertGreaterThan(20, $hits[$user1Id]);
        $this->assertGreaterThan(20, $hits[$user2Id]);
        $this->assertGreaterThan(20, $hits[$user3Id]);
    }

    /**
     * Test execution with a manual users list
     */
    public function SKIP_testExecute_Team()
    {
        // Create a test team_id
        $groupingsLoader = $this->account->getServiceManager()->get(LoaderFactory::class);
        $groupings = $groupingsLoader->get(ObjectTypes::USER, "team_id");
        $group = $groupings->getByName("Test");
        if ($group) {
            $groupings->delete($group->id);
            $groupingsLoader->save($groupings);
        }
        $group = new Group();
        $group->name = "Test";
        $groupings->add($group);
        $groupingsLoader->save($groupings);

        // Create three users for assignment
        $user1 = $this->entityLoader->create(ObjectTypes::USER);
        $user1->setValue("name", "testuser-" . rand());
        $user1->setValue("team_id", $group->id);
        $user1Id = $this->entityLoader->save($user1);
        $this->testEntities[] = $user1;

        $user2 = $this->entityLoader->create(ObjectTypes::USER);
        $user2->setValue("name", "testuser-" . rand());
        $user2->setValue("team_id", $group->id);
        $user2Id = $this->entityLoader->save($user2);
        $this->testEntities[] = $user2;

        $user3 = $this->entityLoader->create(ObjectTypes::USER);
        $user3->setValue("name", "testuser-" . rand());
        $user3->setValue("team_id", $group->id);
        $user3Id = $this->entityLoader->save($user3);
        $this->testEntities[] = $user3;

        $usersArray = [$user1Id, $user2Id, $user3Id];

        // Create new action and set values for the userlist
        $action = $this->getAction();
        $action->setParam('field', 'user_id');
        $action->setParam('team_id', $group->id);

        // Create a test task
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "test");
        $this->entityLoader->save($task);
        $this->testEntities[] = $task;

        // Create a fake WorkFlowInstance since the action does not a saved workflow or instance
        $workFlowInstance = new WorkFlowInstance(123, $task);

        // Now execute the action and make sure it updated the field in $task
        $this->assertTrue($action->execute($workFlowInstance));

        // Make sure the user was assigned to one of the users
        $this->assertTrue(
            in_array($task->getValue("user_id"), $usersArray),
            "Missing user " . $task->getValue("user_id") . " in " . var_export($usersArray, true)
        );

        // Execute repeatedly and check the probability distribution
        $hits = [$user1Id => 0, $user2Id => 0, $user3Id => 0];
        for ($i = 0; $i < 50; $i++) {
            $action->execute($workFlowInstance);
            $hits[$task->getValue('user_id')]++;
        }

        // Make sure probabilities are in acceptable ranges <5 to each since rand is only so random
        $this->assertGreaterThan(5, $hits[$user1Id], var_export($hits, true));
        $this->assertGreaterThan(5, $hits[$user2Id], var_export($hits, true));
        $this->assertGreaterThan(5, $hits[$user3Id], var_export($hits, true));

        // Cleanup
        $groupings->delete($group->id);
        $groupingsLoader->save($groupings);
    }

    /**
     * Test execution with a manual users list
     */
    public function SKIP_testExecute_Group()
    {
        // Create a test team_id
        $groupingsLoader = $this->account->getServiceManager()->get(LoaderFactory::class);
        $groupings = $groupingsLoader->get(ObjectTypes::USER, "groups");
        $group = $groupings->getByName("Test");
        if ($group) {
            $groupings->delete($group->id);
            $groupingsLoader->save($groupings);
        }
        $group = new Group();
        $group->name = "Test";
        $groupings->add($group);
        $groupingsLoader->save($groupings);

        // Create three users for assignment
        $user1 = $this->entityLoader->create(ObjectTypes::USER);
        $user1->setValue("name", "testuser-" . rand());
        $user1->addMultiValue("groups", $group->id);
        $user1Id = $this->entityLoader->save($user1);
        $this->testEntities[] = $user1;

        $user2 = $this->entityLoader->create(ObjectTypes::USER);
        $user2->setValue("name", "testuser-" . rand());
        $user2->addMultiValue("groups", $group->id);
        $user2Id = $this->entityLoader->save($user2);
        $this->testEntities[] = $user2;

        $user3 = $this->entityLoader->create(ObjectTypes::USER);
        $user3->setValue("name", "testuser-" . rand());
        $user3->addMultiValue("groups", $group->id);
        $user3Id = $this->entityLoader->save($user3);
        $this->testEntities[] = $user3;

        $usersArray = [$user1Id, $user2Id, $user3Id];

        // Create new action and set values for the userlist
        $action = $this->getAction();
        $action->setParam('field', 'user_id');
        $action->setParam('group_id', $group->id);

        // Create a test task
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "test");
        $this->entityLoader->save($task);
        $this->testEntities[] = $task;

        // Create a fake WorkFlowInstance since the action does not a saved workflow or instance
        $workFlowInstance = new WorkFlowInstance(123, $task);

        // Now execute the action and make sure it updated the field in $task
        $this->assertTrue($action->execute($workFlowInstance));

        // Make sure the user was assigned to one of the users
        $this->assertTrue(in_array($task->getValue("user_id"), $usersArray));

        // Execute repeatedly and check the probability distribution
        $hits = [$user1Id => 0, $user2Id => 0, $user3Id => 0];
        for ($i = 0; $i < 50; $i++) {
            $action->execute($workFlowInstance);
            $hits[$task->getValue('user_id')]++;
        }

        // It should try to somewhat randomly assign to each of the three passed users
        // Make sure each user got at least 9 entries.
        $this->assertGreaterThan(9, $hits[$user1Id]);
        $this->assertGreaterThan(9, $hits[$user2Id]);
        $this->assertGreaterThan(9, $hits[$user3Id]);

        // Cleanup
        $groupings->delete($group->id);
        $groupingsLoader->save($groupings);
    }
}