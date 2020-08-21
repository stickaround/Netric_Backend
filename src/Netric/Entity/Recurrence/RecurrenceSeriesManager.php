<?php

namespace Netric\Entity\Recurrence;

use Netric\Entity\DataMapper\EntityDataMapperInterface;
use Netric\Entity\EntityInterface;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Entity\EntityLoader;
use Netric\Error;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityDefinition\EntityDefinitionLoader;
use RuntimeException;
use DateTime;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Class creates and deletes entities from a RecurrencePattern series
 */
class RecurrenceSeriesManager implements Error\ErrorAwareInterface
{
    /**
     * Identity mapper for loading/saving/caching recurrence patterns
     *
     * @var RecurrenceIdentityMapper
     */
    private $recurIdentityMapper = null;

    /**
     * Entity DataMapper for saving new entities
     *
     * @var EntityDataMapperInterface
     */
    private $entityDataMapper = null;

    /**
     * Entity loader for loading new entities
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Index for querying entities
     *
     * @var IndexInterface
     */
    private $entityIndex = null;

    /**
     * Loader to get an entity definition
     *
     * @var EntityDefinitionLoader
     */
    private $entityDefinitionLoader = null;

    /**
     * List of errors
     *
     * @var Error\Error
     */
    private $errors = [];

    /**
     * Setup the class
     *
     * @param RecurrenceIdentityMapper $identityMapper For loading/saving/caching patterns
     * @param EntityLoader $entityLoader To load and create new entities
     * @param EntityDataMapperInterface $entityDataMapper To save new entities
     * @param IndexInterface $entityIndex Index used to querying entities
     * @param EntityDefinitionLoader $entityDefinitionLoader
     */
    public function __construct(
        RecurrenceIdentityMapper $identityMapper,
        EntityLoader $entityLoader,
        EntityDataMapperInterface $entityDataMapper,
        IndexInterface $entityIndex,
        EntityDefinitionLoader $entityDefinitionLoader
    ) {
        $this->recurIdentityMapper = $identityMapper;
        $this->entityLoader = $entityLoader;
        $this->entityDataMapper = $entityDataMapper;
        $this->entityIndex = $entityIndex;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
    }

    /**
     * Create all entities in a recurrence pattern up to a specified date
     *
     * @param RecurrencePattern $pattern
     * @param DateTime $toDate
     * @return int number of entities created
     */
    public function createSeries(RecurrencePattern $pattern, DateTime $toDate)
    {
        // Make sure we are working with a valid pattern
        if (!$pattern->validatePattern()) {
            return 0;
        }

        // Make sure this is not being created/removed by any other process
        if ($pattern->isSeriesLocked()) {
            return 0;
        }

        // Lock this pattern to prevent overlap
        $pattern->setSeriesLocked(true);
        $this->recurIdentityMapper->save($pattern);

        // Record the number of entities created
        $numCreated = 0;

        // Get the very next date from the pattern picking up from where it last processed to
        $curDate = $pattern->getNextStart();
        if (!$curDate) {
            return 0;
        }

        // Loop through $pattern->getNextStart() until we have reached $toDate
        while ($curDate <= $toDate) {
            if ($this->createInstance($pattern, $curDate)) {
                $numCreated++;
            }

            // Get the next date to process next time around or exit if we've reached the end
            $curDate = $pattern->getNextStart();
            if (!$curDate) {
                break;
            }
        }

        // Update the date we just processed to and unlock the series
        $pattern->setDateProcessedTo($toDate);
        $pattern->setSeriesLocked(false);
        $this->recurIdentityMapper->save($pattern);

        // Let the caller know how many we just created
        return $numCreated;
    }


    /**
     * Remove a series of entities and the associated recurrence pattern
     *
     * @param EntityInterface $entity Any entity in the series
     * @param UserEntity $user
     * @return bool
     */
    public function removeSeries(EntityInterface $entity, UserEntity $user)
    {
        $recurrencePattern = $entity->getRecurrencePattern();
        $recurRules = $entity->getDefinition()->recurRules;
        $recurId = $recurrencePattern->getId();

        // Only delete recurring entities
        if (!$recurrencePattern) {
            return false;
        }

        // Query entities that are part of the series and delete them
        $query = new EntityQuery($entity->getDefinition()->getObjType(), $user->getAccountId());
        $query->where($recurRules['field_recur_id'])->equals($recurId);
        $result = $this->entityIndex->executeQuery($query);
        $num = $result->getNum();
        for ($i = 0; $i < $num; $i++) {
            $entity = $result->getEntity($i);
            $this->entityLoader->archive($entity, $user);
        }

        // Delete the recurrence pattern
        $this->recurIdentityMapper->delete($recurrencePattern);

        return true;
    }

    /**
     * Get the last error thrown in an object or module
     *
     * @return Error
     */
    public function getLastError()
    {
        return $this->errors[count($this->errors) - 1];
    }

    /**
     * Get all errors
     *
     * @return Error[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Advance a recurring series based on time params in a query.
     *
     * Check to see if any of the where conditions in the query push
     * the field_date_start or field_date_ent into the future.
     *
     * If so, we will advance the recurrencePattern to the date specified
     * in the query.
     *
     * For example, if a user queries for events from 2016-01-01 to 2016-02-01
     * then we need to open any recurring patterns that might have events
     * recurring during those dates and advance them to t 2016-02-01
     * with this->createSeries - 2016-02-01.
     *
     * By doing this we avoid having to create unlimited entiites for
     * never ending recurring patters - we make them just in time as they're
     * being requested.
     *
     * @param EntityQuery $query Check query for conditions that need a series created
     */
    public function createInstancesFromQuery(EntityQuery $query)
    {
        // Get the recur rules for the queried object type
        $def = $this->entityDefinitionLoader->get($query->getObjType());
        $recurRules = $def->recurRules;

        // Do nothing if the object type passed is not recurring
        if (!$recurRules) {
            return;
        }

        /*
         * Loop through each condition to see if it pertains to the start and end date
         * fields for the recurRules
         */
        $processTo = 0;
        $conditions = $query->getWheres();
        foreach ($conditions as $cond) {
            if (
                $cond->fieldName == $recurRules['field_date_start']
                || $cond->fieldName == $recurRules['field_date_end']
            ) {
                if (is_numeric($cond->value)) {
                    // Handle next 'x' number of 'days|weeks|months|years' conditions
                    $inter = "";
                    switch ($cond->operator) {
                        case 'next_x_days':
                            $inter = "days";
                            break;
                        case 'next_x_weeks':
                            $inter = "weeks";
                            break;
                        case 'next_x_months':
                            $inter = "months";
                            break;
                        case 'next_x_years':
                            $inter = "months";
                            break;
                    }

                    // If we have an interval setup for a next_x_* then extend $processTo
                    if ($inter && @strtotime($cond->value) !== false) {
                        $dateString = "+ " . $cond->value . " " . $inter;
                        $processTo = strtotime($dateString, time());
                    } else {
                        // The where condition is probably just a timestamp
                        if ($cond->value > $processTo) {
                            $processTo = $cond->value;
                        }
                    }
                } elseif ($cond->value) {
                    /*
                     * Condition values are text entered by users so let's make sure
                     * is a valid timestamp before processing.
                     */
                    if (@strtotime($cond->value) !== false) {
                        /*
                         * If $cond->value contains a timestamp later than any
                         * previously entered (or any at all), then update $processTo
                         */
                        $valTimestamp = strtotime($cond->value);
                        if ($valTimestamp > $processTo) {
                            $processTo = $valTimestamp;
                        }
                    }
                }
            }
        }

        // If any of the conditions contained values impacted by recurRules then process
        if ($processTo > 0) {
            $dateTo = new \DateTime();
            $dateTo->setTimestamp($processTo);
            $objType = $def->getObjType();
            $recurPatterns = $this->recurIdentityMapper->getStalePatterns($objType, $dateTo, $query->getAccountId());
            foreach ($recurPatterns as $pattern) {
                $this->createSeries($pattern, $dateTo);
            }
        }
    }

    /**
     * Create an instance of an entity in a recurring pattern
     *
     * @param RecurrencePattern $recurrencePattern The recurring pattern to get rules from
     * @param DateTime $date The date of the instance to create
     * @return bool|string false if fail, unique id of new saved entity if success
     */
    private function createInstance(RecurrencePattern $recurrencePattern, DateTime $date)
    {
        $firstEntity = $this->entityLoader->getEntityById(
            $recurrencePattern->getFirstEntityId(),
            $recurrencePattern->getAccountId()
        );
        if (!$firstEntity) {
            throw new RuntimeException(
                'First entity in recurrence not found ' . $recurrencePattern->getFirstEntityId()
            );
        }

        $objType = $firstEntity->getDefinition()->getObjType();
        // If the first entity no longer exists then we should cancel this recurrence pattern
        if (!$firstEntity) {
            return false;
        }

        $newInstanceEntity = $this->entityLoader->create($objType, $recurrencePattern->getAccountId());
        $entityDefinition = $firstEntity->getDefinition();
        $recurRules = $entityDefinition->recurRules;

        // Validate
        if (!isset($recurRules)) {
            throw new \RuntimeException("Cannot get recurRules for objType: " . $objType);
        }
        if (!isset($recurRules['field_date_start'])) {
            throw new \RuntimeException("field_date_start was not provided in recur rules");
        }

        // Clone all values from firstEntity into newInstanceEntity
        $firstEntity->cloneTo($newInstanceEntity);

        /*
         * Set the start and end date/time(s) of the cloned entity to $date
         */
        $newStartDate = new \DateTime();

        // TODO: We should merge these fields, there's no reason to separate timestamps and dates
        // TODO: It is left here for legacy reasons only.
        $useStartField = ($recurRules['field_time_start'])
            ? $recurRules['field_time_start'] : $recurRules['field_date_start'];
        $useEndField = ($recurRules['field_time_end'])
            ? $recurRules['field_time_end'] : $recurRules['field_date_end'];

        // Create new start date
        $newStartDate = $this->createRelativeDate($firstEntity, $date, $useStartField);

        // Update the new entity to reflect the new date
        $newInstanceEntity->setValue($useStartField, $newStartDate->getTimestamp());

        // Create new relative end date
        $newEndDate = $this->createRelativeDate($firstEntity, $date, $useEndField);

        // Update the new entity to reflect the new date
        $newInstanceEntity->setValue($useEndField, $newEndDate->getTimestamp());

        /*
         * Clone will copy the reference of the old recurrence pattern to the new
         * instance. We do not have to do anything to keep the dataMapper from trying
         * to re-create an instance from the recurrence pattern because it is currently
         * locked in $this->createSeries(). Otherwise saving an entity may trigger another
         * process to create the series starting from the new entity forward which would get ugly.
         */

        // Send new entity id back
        if ($firstEntity->getOwnerId()) {
            $user = $this->entityDataMapper->getEntityById(
                $firstEntity->getOwnerId(),
                $recurrencePattern->getAccountId()
            );
        }

        // If there is no user then set it to system
        if (!$user) {
            $user = $this->entityDataMapper->getByUniqueName(
                ObjectTypes::USER,
                UserEntity::USER_SYSTEM,
                $recurrencePattern->getAccountId()
            );
        }

        return $this->entityDataMapper->save($newInstanceEntity, $user);
    }

    /**
     * Generate a new date relative to the first entities value
     *
     * @param EntityInterface $firstEntity
     * @param \DateTime $date
     * @param $useTimeField
     * @return \DateTime
     */
    private function createRelativeDate(EntityInterface $firstEntity, \DateTime $date, $useTimeField)
    {
        $relativeDate = new \DateTime();

        // Cannot recur if there is no start or end date for the entity
        if (!$firstEntity->getValue($useTimeField)) {
            throw new \RuntimeException(
                $useTimeField . " is required and not set in entity" .
                    $firstEntity->getDefinition()->getObjType() . ":" . $firstEntity->getEntityId()
            );
        }

        // We may be getting both date and time from a single timestamp field
        $relativeDate->setTimestamp($firstEntity->getValue($useTimeField));

        // Now modify the date portion only from $date
        $relativeDate->setDate($date->format('Y'), $date->format('m'), $date->format('d'));

        return $relativeDate;
    }
}
