<?php

/**
 * Test entity task class
 */

namespace NetricTest\Entity\ObjType;

use Netric\Entity\ObjType\UserEntity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\TaskEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Entity\Recurrence\RecurrencePattern;
use Netric\Entity\DataMapper\EntityDataMapperFactory;

class TaskTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Administrative user
     *
     * @var \Netric\Task
     */
    private $tasks = [];

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    protected function tearDown(): void
    {
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        foreach ($this->tasks as $task) {
            $dm->delete($task, $this->account->getAuthenticatedUser());
        }
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::TASK, $this->account->getAccountId());
        $this->assertInstanceOf(TaskEntity::class, $entity);
    }

    public function testOnRecurrence()
    {
        // Setup entity datamapper for handling users
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);

        $rp = new RecurrencePattern($this->account->getAccountId());
        $rp->setRecurType(RecurrencePattern::RECUR_DAILY);
        $rp->setInterval(1);
        $rp->setDateStart(new \DateTime("1/2/2010"));
        $rp->setDateEnd(new \DateTime("3/1/2010"));

        $task = $loader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue("name", "Test Task Recurrence");
        $task->setValue("deadline", strtotime("01 January 2010"));
        $task->setRecurrencePattern($rp);
        $taskId = $dm->save($task, $this->account->getSystemUser());
        $this->tasks[] = $task;

        // Use the loader to get the task entity with recurrence
        $ent = $loader->getEntityById($taskId, $this->account->getAccountId());
        $taskRecurrence = $ent->getRecurrencePattern();

        // First instance should be today
        $tsNext = $taskRecurrence->getNextStart();
        $this->assertEquals($tsNext, new \DateTime("01/02/2010"));

        // Next instance should be tomorrow
        $tsNext = $taskRecurrence->getNextStart();
        $this->assertEquals($tsNext, new \DateTime("01/03/2010"));

        // Change interval to skip a day and rewind to set
        $taskRecurrence->setInterval(2);
        $tsNext = $taskRecurrence->getNextStart();
        $this->assertEquals($tsNext, new \DateTime("01/05/2010"));

        // Call again should skip another day
        $tsNext = $taskRecurrence->getNextStart();
        $this->assertEquals($tsNext, new \DateTime("01/07/2010"));

        $noDeadlineTask = $loader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $noDeadlineTask->setValue("name", "Task Without Deadline");
        $noDeadlineTask->setRecurrencePattern($rp);

        // This should throw an exception since it requires a task deadline when setting a recurrence
        $this->expectException(\RuntimeException::class);
        $dm->save($noDeadlineTask, $this->account->getSystemUser());
        $this->tasks[] = $noDeadlineTask;
    }
}
