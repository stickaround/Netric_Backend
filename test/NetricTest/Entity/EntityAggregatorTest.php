<?php

/**
 * Make sure related entity fields are aggregated
 */

namespace NetricTest\Entity;

use Netric\Entity;
use Netric\Entity\EntityAggregator;
use Netric\Entity\EntityLoader;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityAggregatorFactory;
use Netric\Entity\ObjType\UserEntity;
use NetricTest\Bootstrap;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

class EntityAggregatorTest extends TestCase
{
    /**
     * Tenant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Form service
     *
     * @var EntityAggregator
     */
    private $entityAggregator = null;

    /**
     * Entity Loader
     *
     * @var Entityloader
     */
    private $entityLoader = null;

    /**
     * Administrative user
     *
     * We test for this user since he will never have customized forms
     *
     * @var \Netric\User
     */
    private $user = null;

    /**
     * Test entities create
     *
     * @var Entity[]
     */
    private $testEntities = array();

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $sm = $this->account->getServiceManager();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
        $this->entityAggregator = $sm->get(EntityAggregatorFactory::class);
        $this->entityLoader = $sm->get(EntityLoaderFactory::class);
    }

    /**
     * Cleanup any created entities
     */
    protected function tearDown(): void
    {
        foreach ($this->testEntities as $entity) {
            $this->entityLoader->delete($entity, true);
        }
    }

    public function testUpdateAggregates_Sum()
    {
        // Create a new task
        $task = $this->entityLoader->create(ObjectTypes::TASK);
        $task->setValue("name", "utest aggregates-sum");
        $tid = $this->entityLoader->save($task);
        $this->testEntities[] = $task;

        // Add two time entries - saving calls trigger aggregator
        $time = $this->entityLoader->create(ObjectTypes::TIME);
        $time->setValue("task_id", $tid);
        $time->setValue("hours", 1);
        $this->entityLoader->save($time);
        $this->testEntities[] = $time;

        $time2 = $this->entityLoader->create(ObjectTypes::TIME);
        $time2->setValue("task_id", $tid);
        $time2->setValue("hours", 1);
        $this->entityLoader->save($time2);
        $this->testEntities[] = $time2;

        // Now check if the task has 2 hours in the cost_actual field
        $task = $this->entityLoader->getByGuid($tid);
        $this->assertEquals(2, $task->getValue("cost_actual"));
    }

    public function testUpdateAggregates_Avg()
    {

        // Create a new products
        $task = $this->entityLoader->create(ObjectTypes::PRODUCT);
        $task->setValue("name", "utest aggregates-avg");
        $pid = $this->entityLoader->save($task);
        $this->testEntities[] = $task;

        // Add two rating entries - saving calls trigger aggregator
        $rating1 = $this->entityLoader->create(ObjectTypes::PRODUCT_REVIEW);
        $rating1->setValue(ObjectTypes::PRODUCT, $pid);
        $rating1->setValue("rating", 8);
        $this->entityLoader->save($rating1);
        $this->testEntities[] = $rating1;

        $rating2 = $this->entityLoader->create(ObjectTypes::PRODUCT_REVIEW);
        $rating2->setValue(ObjectTypes::PRODUCT, $pid);
        $rating2->setValue("rating", 2);
        $this->entityLoader->save($rating2);
        $this->testEntities[] = $rating2;

        // The product rating should be an avg of 5 (8 + 2) / 2 ratings
        $task = $this->entityLoader->getByGuid($pid);
        $this->assertEquals(5, $task->getValue("rating"));
    }
}
