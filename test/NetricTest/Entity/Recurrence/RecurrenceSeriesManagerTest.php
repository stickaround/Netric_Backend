<?php

/**
 * Test saving and loading recurrence patterns from the DataMapper
 */

namespace NetricTest\Entity\Recurrence;

use Netric\Entity\Recurrence;
use Netric\Entity\Recurrence\RecurrencePattern;
use Netric\Entity;
use Netric\Entity\EntityLoader;
use Netric\EntityQuery;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\DataMapper\DataMapperFactory;
use Netric\Entity\Recurrence\RecurrenceSeriesManagerFactory;
use Netric\Entity\Recurrence\RecurrenceIdentityMapperFactory;
use Netric\EntityDefinition\ObjectTypes;


class RecurrenceSeriesManagerTest extends TestCase
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
     * @var \Netric\User
     */
    private $user = null;

    /**
     * Datamapper for saving entities
     *
     * @var Entity\DataMapperInterface
     */
    private $entityDataMapper = null;

    /**
     * Entity loader
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Recurrence series manager to test
     *
     * @var Recurrence\RecurrenceSeriesManager
     */
    private $recurSeriesManager = null;

    /**
     * Recurrence identity mapper
     *
     * @var Recurrence\RecurrenceIdentityMapper
     */
    private $recurIndentityMapper = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);

        $sm = $this->account->getServiceManager();
        $this->entityDataMapper = $sm->get(DataMapperFactory::class);
        $this->entityLoader = $sm->get(EntityLoaderFactory::class);
        $this->recurSeriesManager = $sm->get(RecurrenceSeriesManagerFactory::class);
        $this->recurIndentityMapper = $sm->get(RecurrenceIdentityMapperFactory::class);
    }

    /**
     * Test that the series creator can generate a series of entities based on a recurring pattern
     *
     * We do not need to test all permutations of different kinds of recurrence since
     * the complexity of that is almost all handled in RecurrencePattern::getNextDate.
     * For more details please look at the RecurrencePatternTest.php file in this directory.
     */
    public function testCreateSeries()
    {
        // Save a new entity with the recurrence pattern
        $entityData = array(
            "name" => 'my recurring event',
            "ts_start" => strtotime("2016-01-01 08:00:00 PST"),
            "ts_end" => strtotime("2016-01-01 09:00:00 PST"),
            "recurrence_pattern" => array(
                "recur_type" => Recurrence\RecurrencePattern::RECUR_DAILY,
                "interval" => 1,
                "date_start" => "2016-01-01",
                "date_end" => "2016-01-05",
            )
        );
        $event = $this->entityLoader->create(ObjectTypes::CALENDAR_EVENT);
        $event->fromArray($entityData);
        $this->entityDataMapper->save($event);

        $recurRules = $event->getDefinition()->recurRules;
        $recurrencePattern = $event->getRecurrencePattern();

        // Test that references was properly created
        $this->assertEquals($recurrencePattern->getFirstEntityId(), $event->getEntityId());
        $this->assertEquals($recurrencePattern->getId(), $event->getValue($recurRules['field_recur_id']));

        // Create the series for +1 day from start - should create one instance
        $dateTo = new \DateTime("2016-01-02");
        $numCreated = $this->recurSeriesManager->createSeries($recurrencePattern, $dateTo);
        $this->assertEquals(1, $numCreated);

        // Create the series for +5 days from start - should create four instances after the above
        $dateTo = new \DateTime("2016-01-05");
        $numCreated = $this->recurSeriesManager->createSeries($recurrencePattern, $dateTo);
        $this->assertEquals(3, $numCreated);

        // Delete the series
        $this->recurSeriesManager->removeSeries($event);
    }

    /**
     * Even though it is private, we need to test that the createInstance function works
     */
    public function testCreateInstance()
    {
        // Gain access to the private createInstance function
        $refSeriesManager = new \ReflectionObject($this->recurSeriesManager);
        $createInstance = $refSeriesManager->getMethod("createInstance");
        $createInstance->setAccessible(true);

        // Save a new entity with the recurrence pattern
        $entityData = [
            "name" => 'my recurring event',
            "ts_start" => strtotime("2016-01-01 08:00:00 PST"),
            "ts_end" => strtotime("2016-01-01 09:00:00 PST"),
            "recurrence_pattern" => [
                "recur_type" => RecurrencePattern::RECUR_DAILY,
                "interval" => 1,
                "date_start" => "2016-01-01",
                "date_end" => "2016-01-05",
            ]
        ];
        $event = $this->entityLoader->create(ObjectTypes::CALENDAR_EVENT);
        $event->fromArray($entityData);
        $this->entityDataMapper->save($event);

        // Create instance for next day
        $recurPattern = $event->getRecurrencePattern();
        $nextDay = new \DateTime("2016-01-02");
        $eid = $createInstance->invoke($this->recurSeriesManager, $recurPattern, $nextDay);
        $this->assertNotEmpty($eid);

        // Open the new entity
        $event2 = $this->entityLoader->getByGuid($eid);
        $this->assertEquals($event->getName(), $event2->getName());

        // Make sure the dates are different but the times are the same
        $firstStart = new \DateTime();
        $firstStart->setTimestamp($event->getvalue("ts_start"));
        $secondStart = new \DateTime();
        $secondStart->setTimestamp($event2->getvalue("ts_start"));
        $this->assertEquals($nextDay->format("Y-m-d"), $secondStart->format("Y-m-d"));
        $this->assertEquals($firstStart->format("H:m:s"), $secondStart->format("H:m:s"));

        $firstEnd = new \DateTime();
        $firstEnd->setTimestamp($event->getvalue("ts_end"));
        $secondEnd = new \DateTime();
        $secondEnd->setTimestamp($event2->getvalue("ts_end"));
        $this->assertEquals($nextDay->format("Y-m-d"), $secondEnd->format("Y-m-d"));
        $this->assertEquals($firstEnd->format("H:m:s"), $secondEnd->format("H:m:s"));

        // Cleanup
        $this->entityDataMapper->delete($event, true);
    }

    public function testRemoveSeries()
    {
        // Save a new entity with the recurrence pattern
        $entityData = [
            "name" => 'my recurring event',
            "ts_start" => strtotime("2016-01-01 08:00:00 PST"),
            "ts_end" => strtotime("2016-01-01 09:00:00 PST"),
            "recurrence_pattern" => [
                "recur_type" => RecurrencePattern::RECUR_DAILY,
                "interval" => 1,
                "date_start" => "2016-01-01",
                "date_end" => "2016-01-05",
            ]
        ];
        $event = $this->entityLoader->create(ObjectTypes::CALENDAR_EVENT);
        $event->fromArray($entityData);
        $eventId = $this->entityDataMapper->save($event);
        $recurId = $event->getRecurrencePattern()->getId();

        // Create the series for +5 days from start - should create five instances
        $dateTo = new \DateTime("2016-01-05");
        $numCreated = $this->recurSeriesManager->createSeries($event->getRecurrencePattern(), $dateTo);

        // Delete the series
        $ret = $this->recurSeriesManager->removeSeries($event);
        $this->assertTrue($ret);

        // Try to open the original and make sure it is deleted
        $this->assertTrue($this->entityLoader->getByGuid($eventId)->isDeleted());

        // Make sure the recurring pattern was also deleted
        $this->assertNull($this->recurIndentityMapper->getById($recurId));

        // Cleanup
        $this->entityDataMapper->delete($event, true);
    }

    public function testCreateInstancesFromQuery()
    {
        // Save a new entity with the recurrence pattern
        $entityData = array(
            "name" => 'my recurring event',
            "ts_start" => strtotime("2016-01-01 08:00:00 PST"),
            "ts_end" => strtotime("2016-01-01 09:00:00 PST"),
            "recurrence_pattern" => array(
                "recur_type" => Recurrence\RecurrencePattern::RECUR_DAILY,
                "interval" => 1,
                "date_start" => "2016-01-01",
                "date_end" => "2016-01-05",
            )
        );
        $event = $this->entityLoader->create(ObjectTypes::CALENDAR_EVENT);
        $event->fromArray($entityData);
        $this->entityDataMapper->save($event);
        $recurId = $event->getRecurrencePattern()->getId();

        // Create a query that gets events from January 1 to January 5
        $dateTo = new \DateTime("2016-01-05");
        $query = new EntityQuery(ObjectTypes::CALENDAR_EVENT);
        $query->where("ts_start")->isLessThan($dateTo->format("Y-m-d"));

        // Have the manager create instances using the query
        $this->recurSeriesManager->createInstancesFromQuery($query);

        // Open the recurrence pattern and make sure to date processed to was moved out
        $recurrencePattern = $this->recurIndentityMapper->getById($recurId);
        $dateProcessedTo = $recurrencePattern->getDateProcessedTo();
        $this->assertEquals($dateProcessedTo->format("Y-m-d"), $dateTo->format("Y-m-d"));

        // Cleanup
        $this->entityDataMapper->delete($event, true);
    }
}
