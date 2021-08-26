<?php

namespace NetricTest\WorkerMan\Worker;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\Entity\Notifier\Notifier;
use Netric\Entity\ObjType\UserEntity;
use Netric\Log\LogInterface;
use Netric\WorkerMan\Job;
use Netric\WorkerMan\Worker\NotificationWorker;
use PHPUnit\Framework\TestCase;

/**
 * @group integration
 */
class NotificationWorkerTest extends TestCase
{
    public function testWork()
    {
        // Setup mock services
        $log = $this->createMock(LogInterface::class);
        $mockEntityLoader = $this->createMock(EntityLoader::class);
        $notifier = $this->createMock(Notifier::class);

        // Create a few test variables
        $accountId = 'UUID-TEST-ACCOUNT';
        $userId = 'UUID-USER';
        $entityId = 'UUID-ENTITY';
        $user = $this->createMock(UserEntity::class);
        $testEntity = $this->createMock(EntityInterface::class);

        // Make sure we can return the test entities
        $mockEntityLoader->method('getEntityById')->will($this->returnValueMap([
            [$userId, $accountId, $user],
            [$entityId, $accountId, $testEntity],
        ]));

        // Setup the worker and job
        $job = new Job();
        $job->setWorkload([
            'account_id' => $accountId,
            'entity_id' => $entityId,
            'user_id' => $userId,
            'event_name' => 'sent',
            'changed_description' => 'Did something'
        ]);

        $worker = new NotificationWorker($notifier, $mockEntityLoader, $log);

        // Make sure send gets called
        $notifier->expects($this->once())->method('send');

        // Make sure it is a success
        $this->assertTrue($worker->work($job));
    }

    public function testWorkBadWorkload()
    {
        // Setup mock services
        $log = $this->createMock(LogInterface::class);
        $mockEntityLoader = $this->createMock(EntityLoader::class);
        $notifier = $this->createMock(Notifier::class);

        // Setup the worker and job
        $job = new Job();
        $job->setWorkload([
            'account_id' => '',
            'entity_id' => '',
            'user_id' => '',
            'event_name' => '',
            'changed_description' => ''
        ]);

        $worker = new NotificationWorker($notifier, $mockEntityLoader, $log);

        // Make sure it is a success
        $this->assertFalse($worker->work($job));
    }
}
