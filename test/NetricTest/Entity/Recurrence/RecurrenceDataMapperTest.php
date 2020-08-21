<?php

/**
 * Test saving and loading recurrence patterns from the DataMapper
 */

namespace NetricTest\Entity\Recurrence;

use Netric\Entity\Recurrence\RecurrenceRdbDataMapper;
use Netric\Entity\Recurrence\RecurrenceDataMapperFactory;
use Netric\Entity\Recurrence\RecurrencePattern;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\ObjType\UserEntity;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

class RecurrenceDataMapperTest extends TestCase
{
    /**
     * Arbitrary test ID(s)
     */
    const TEST_ENTITY_ID = '14e6e6b6-d345-4e7c-b8d0-67cccc71e152';

    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Administrative user
     *
     * @var \Netric\User
     */
    private $user = null;

    /**
     * DataMapper to test
     *
     * @var RecurrenceDataMapper
     */
    private $dataMapper = null;

    /**
     * Test recurrence patterns created that need to be cleaned up
     *
     * @var RecurrencePattern[]
     */
    private $testRecurrence = [];

    /**
     * Entity definition for tasks
     *
     * @var EntityDefinition
     */
    private $taskEntityDefintion = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);

        // Get service manager for locading dependencies
        $sm = $this->account->getServiceManager();

        // Setup the recurrence datamapper
        $entDefLoader = $sm->get(EntityDefinitionLoaderFactory::class);
        $this->dataMapper = $sm->get(RecurrenceDataMapperFactory::class);

        // Create entity definition for tasks
        $this->taskEntityDefintion = $entDefLoader->get(ObjectTypes::TASK);
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(RecurrenceRdbDataMapper::class, $this->dataMapper);
    }

    /**
     * Cleanup any test entities we created
     */
    protected function tearDown(): void
    {
        foreach ($this->testRecurrence as $rp) {
            $this->dataMapper->delete($rp);
        }
    }

    public function testSave()
    {
        $rp = new RecurrencePattern($this->account->getAccountId());
        $rp->setObjTypeId($this->taskEntityDefintion->getEntityDefinitionId());
        $rp->setRecurType(RecurrencePattern::RECUR_DAILY);
        $rp->setInterval(1);
        $rp->setDateStart(new \DateTime("1/1/2010"));
        $rp->setDateEnd(new \DateTime("3/1/2010"));

        $rid = $this->dataMapper->save($rp);
        $this->testRecurrence[] = $rp;

        $this->assertNotNull($rid);
    }

    public function testUpdateParentObjectId()
    {
        $rp = new RecurrencePattern($this->account->getAccountId());
        $rp->setObjTypeId($this->taskEntityDefintion->getEntityDefinitionId());
        $rp->setRecurType(RecurrencePattern::RECUR_DAILY);
        $rp->setInterval(1);
        $rp->setDateStart(new \DateTime("1/1/2010"));
        $rp->setDateEnd(new \DateTime("3/1/2010"));

        $rid = $this->dataMapper->save($rp);
        $this->assertNotNull($rid);

        $result = $this->dataMapper->updateParentEntityId($rid, self::TEST_ENTITY_ID, $rp->getAccountId());
        $this->assertTrue($result);
    }

    public function testLoad()
    {
        $data = [
            "recur_type" => RecurrencePattern::RECUR_WEEKLY,
            "entity_definition_id" => $this->taskEntityDefintion->getEntityDefinitionId(),
            "interval" => 1,
            "date_start" => "2015-01-01",
            "date_end" => "2015-03-01",
            "day_of_week_mask" => RecurrencePattern::WEEKDAY_SUNDAY,
        ];
        $rp = new RecurrencePattern($this->account->getAccountId());
        $rp->fromArray($data);

        $rid = $this->dataMapper->save($rp);
        $this->testRecurrence[] = $rp;

        $this->assertNotNull($rid);

        $opened = $this->dataMapper->load($rid, $this->account->getAccountId());

        $this->assertNotEmpty($opened->getId());
        $this->assertEquals($data['recur_type'], $opened->getRecurType());
        $this->assertEquals($data['interval'], $opened->getInterval());
        $this->assertEquals(new \DateTime($data['date_start']), $opened->getDateStart());
        $this->assertEquals(new \DateTime($data['date_end']), $opened->getDateEnd());
        $this->assertEquals(1, $opened->getDayOfWeekMask() & RecurrencePattern::WEEKDAY_SUNDAY);
    }

    public function testGetNextId()
    {
        $lastId = $this->dataMapper->getNextId();
        $nextId = $this->dataMapper->getNextId();
        $this->assertNotEquals($lastId, $nextId);
    }

    public function testDelete()
    {
        // Create
        $data = [
            "recur_type" => RecurrencePattern::RECUR_DAILY,
            "entity_definition_id" => $this->taskEntityDefintion->getEntityDefinitionId(),
            "interval" => 1,
            "date_start" => "2015-01-01",
            "date_end" => "2015-03-01"
        ];
        $rp = new RecurrencePattern($this->account->getAccountId());
        $rp->fromArray($data);
        $rid = $this->dataMapper->save($rp);
        $this->testRecurrence[] = $rp;

        // Delete
        $this->dataMapper->delete($rp);

        // Assure we cannot load it
        $this->assertNull($this->dataMapper->load($rid, $this->account->getAccountId()));
    }

    public function testDeleteById()
    {
        // Create
        $data = [
            "recur_type" => RecurrencePattern::RECUR_DAILY,
            "entity_definition_id" => $this->taskEntityDefintion->getEntityDefinitionId(),
            "interval" => 1,
            "date_start" => "2015-01-01",
            "date_end" => "2015-03-01"
        ];
        $rp = new RecurrencePattern($this->account->getAccountId());
        $rp->fromArray($data);
        $rid = $this->dataMapper->save($rp);
        $this->testRecurrence[] = $rp;

        // Delete
        $this->dataMapper->deleteById($rid, $this->account->getAccountId());

        // Assure we cannot load it
        $this->assertNull($this->dataMapper->load($rid, $this->account->getAccountId()));
    }

    /**
     * Test query to get a list of stale patterns compared to a specific date
     *
     * This basiclaly means the recurrencePattern processedTo is earlier
     * than the challenge date specified.
     */
    public function testGetStalePatterns()
    {
        // Create
        $data = [
            "recur_type" => RecurrencePattern::RECUR_DAILY,
            "entity_definition_id" => $this->taskEntityDefintion->getEntityDefinitionId(),
            "interval" => 1,
            "date_start" => "2015-02-01",
            "date_end" => "2015-03-01",
            "date_processed_to" => "2015-02-01",
        ];
        $rp = new RecurrencePattern($this->account->getAccountId());
        $rp->fromArray($data);
        $rid = $this->dataMapper->save($rp);
        $this->testRecurrence[] = $rp;

        // Check before date-start, $rid should not be returned
        $dateTo = new \DateTime("2015-01-01");
        $staleIds = $this->dataMapper->getStalePatternIds(ObjectTypes::TASK, $dateTo, $rp->getAccountId());
        $this->assertFalse(in_array($rid, $staleIds));

        // Check the day after start date which should create entities
        $dateTo = new \DateTime("2015-02-02");
        $staleIds = $this->dataMapper->getStalePatternIds(ObjectTypes::TASK, $dateTo, $rp->getAccountId());
        $this->assertTrue(in_array($rid, $staleIds));

        // Check a couple days out which should also return the above
        $dateTo = new \DateTime("2015-02-20");
        $staleIds = $this->dataMapper->getStalePatternIds(ObjectTypes::TASK, $dateTo, $rp->getAccountId());
        $this->assertTrue(in_array($rid, $staleIds));

        // Go beyond the end date which should NOT return the above pattern
        $dateTo = new \DateTime("2015-05-01");
        $staleIds = $this->dataMapper->getStalePatternIds(ObjectTypes::TASK, $dateTo, $rp->getAccountId());
        $this->assertFalse(in_array($rid, $staleIds));

        // Cleanup
        $this->dataMapper->deleteById($rid, $this->account->getAccountId());
    }
}
