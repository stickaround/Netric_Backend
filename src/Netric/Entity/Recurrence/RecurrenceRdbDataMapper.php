<?php

namespace Netric\Entity\Recurrence;

use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Db\Relational\RelationalDbContainerInterface;
use Netric\Db\Relational\RelationalDbContainer;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Error;
use Netric\DataMapperAbstract;
use Ramsey\Uuid\Uuid;

/**
 * Relational Database DataMapper for recurrence pattern
 */
class RecurrenceRdbDataMapper extends DataMapperAbstract
{
    /**
     * Database container
     *
     * @var RelationalDbContainerInterface
     */
    private $databaseContainer = null;

    /**
     * Entity definition loader
     *
     * This is mostly used to get the id of a textual objType
     *
     * @var EntityDefinitionLoader
     */
    private $entityDefinitionLoader = null;

    /**
     * Last error message
     *
     * @var string
     */
    private $lastError = "";

    /**
     * Define table names
     */
    const ENTITY_RECUR_TABLE = 'entity_recurrence';

    /**
     * Class constructor to set up dependencies
     *
     * @param RelationalDbContainer $database Handles the database actions     
     * @param EntityDefinitionLoader $defLoader Handles the loading of entity definition     
     */
    public function __construct(
        RelationalDbContainer $dbContainer,
        EntityDefinitionLoader $entityDefinitionLoader
    ) {
        $this->databaseContainer = $dbContainer;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
    }

    /**
     * Get active database handle
     *
     * @param string $accountId the account that this pattern belongs to
     * @return RelationalDbInterface
     */
    private function getDatabase(string $accountId): RelationalDbInterface
    {
        return $this->databaseContainer->getDbHandleForAccountId($accountId);
    }

    /**
     * Save a recurrence pattern to the database
     *
     * When the pattern is saved for the first time, it can use the $useId field
     * to see if it should be using a reserved ID or request a new one. This is
     * sometimes used when we need to save a reference to a recurrence in an entity
     * before saving the details of said recurrence.
     *
     * @param RecurrencePattern $recurPattern The recurrence pattern we are going to save
     * @param string $useId We can reserve an ID to use when creating a new instace via getNextId()
     * 
     * @return string Unique id of the pattern on success or null on failure this $this->lastError set
     * @throws \InvalidArgumentException in the instance that the pattern is not valid
     * @throws \RuntimeException if saving failed for some reason
     */
    public function save(RecurrencePattern $recurPattern, $useId = null)
    {
        if (!$recurPattern->validatePattern()) {
            throw new \InvalidArgumentException($recurPattern->getLastError()->getMessage());
        }

        // Get the unique id of the account that this pattern belongs to
        $accountId = $recurPattern->getAccountId();
        $data = $recurPattern->toArray();
        $dayOfWeekMask = $recurPattern->getDayOfWeekMask();

        if (!$data['entity_definition_id']) {
            throw new \InvalidArgumentException("No object type set for recurring pattern");
        }

        $recurrenceData = [
            'entity_recurrence_id' => $data['entity_recurrence_id'],
            'account_id' => $accountId,
            'entity_definition_id' => $data['entity_definition_id'],
            'date_processed_to' => $data['date_processed_to'],
            'parent_entity_id' => $data['first_entity_id'],
            'type' => $data['recur_type'],
            'interval' => $data['interval'],
            'date_start' => $data['date_start'],
            'date_end' => $data['date_end'],
            'dayofmonth' => $data['day_of_month'],
            'instance' => $data['instance'],
            'monthofyear' => $data['month_of_year'],
            'f_active' => $data['f_active'],
            'ep_locked' => $data['ep_locked'],
        ];

        $daysOfWeekMaskData["1"] = $dayOfWeekMask & RecurrencePattern::WEEKDAY_SUNDAY;
        $daysOfWeekMaskData["2"] = $dayOfWeekMask & RecurrencePattern::WEEKDAY_MONDAY;
        $daysOfWeekMaskData["3"] = $dayOfWeekMask & RecurrencePattern::WEEKDAY_TUESDAY;
        $daysOfWeekMaskData["4"] = $dayOfWeekMask & RecurrencePattern::WEEKDAY_WEDNESDAY;
        $daysOfWeekMaskData["5"] = $dayOfWeekMask & RecurrencePattern::WEEKDAY_THURSDAY;
        $daysOfWeekMaskData["6"] = $dayOfWeekMask & RecurrencePattern::WEEKDAY_FRIDAY;
        $daysOfWeekMaskData["7"] = $dayOfWeekMask & RecurrencePattern::WEEKDAY_SATURDAY;

        if ($recurPattern->getId()) {
            $sql = 'SELECT entity_recurrence_id FROM ' .
                self::ENTITY_RECUR_TABLE .
                ' WHERE entity_recurrence_id=:entity_recurrence_id';
            $result = $this->getDatabase($accountId)->query($sql, ["entity_recurrence_id" => $recurPattern->getId()]);

            if ($result->rowCount()) {
                /*
                 * It is possible that the id of the recurrence pattern was pre-set with the
                 * next unique id prior to it having been saved in the database. If this is the
                 * case we will need to make sure we include the id in the insert statement
                 * because we cannot ever assume that if it already has an id that it was previously saved.
                 */
                $recurrenceData['entity_recurrence_id'] = $recurPattern->getId();

                // Add update statements for recurrence fields
                $updateStatements = [];
                foreach ($recurrenceData as $colName => $colValue) {
                    $updateStatements[] = "$colName=:$colName";
                }

                // Add update statements for days of week mask fields
                $updateParams = $recurrenceData;
                foreach ($daysOfWeekMaskData as $index => $value) {
                    $updateStatements[] = "dayofweekmask[$index]=:dayofweekmask_$index";
                    $updateParams["dayofweekmask_$index"] = ($value > 0) ? true : false;
                }

                $sql = 'UPDATE ' . self::ENTITY_RECUR_TABLE . ' SET ';
                $sql .= implode(',', $updateStatements);
                $sql .= " WHERE entity_recurrence_id=:entity_recurrence_id";

                // Run the update and return the id as the result
                $result = $this->getDatabase($accountId)->query($sql, $updateParams);

                return $recurPattern->getId();
            }
        }

        // Override the recurrence id if the $useId is set
        if ($useId) {
            $recurrenceData["entity_recurrence_id"] = $useId;
        }

        // get/set entity_recurrence_id
        if (empty($recurrenceData['entity_recurrence_id'])) {
            $recurrenceData['entity_recurrence_id'] = $this->getNextId();
        }

        $insertColumns = array_keys($recurrenceData);
        $insertParams = $insertColumns;
        foreach ($daysOfWeekMaskData as $index => $value) {
            $insertColumns[] = "dayofweekmask[$index]";
            $insertParams[] = "dayofweekmask_$index";
            $recurrenceData["dayofweekmask_$index"] = ($value > 0) ? true : false;
        }

        $sql = 'INSERT INTO ' . self::ENTITY_RECUR_TABLE . ' (' . implode(",", $insertColumns) . ")";
        // Add values as params by prefixing each with ':'
        $sql .= " VALUES(:" . implode(",:", $insertParams) . ")";

        // Run query, get next value (if selected), and commit
        $this->getDatabase($accountId)->query($sql, $recurrenceData);


        // If the recurrence pattern do not have an Id, then set it with the newly created id
        if (!$recurPattern->getId()) {
            $recurPattern->setId($recurrenceData["entity_recurrence_id"]);
        }

        return $recurPattern->getId();
    }

    /**
     * Secure a unique id to use before it is saved
     *
     * @return string
     */
    public function getNextId(): string
    {
        return Uuid::uuid4()->toString();
    }

    /**
     * Load up an entity recurrence pattern by id
     *
     * @param string $recurId The unique id of the pattern to load
     * @param string $accountId Unique id of the account that this pattern belongs to
     * 
     * @return RecurrencePattern
     * @throws \InvalidArgumentException if the id passed is not a valid number
     */
    public function load(string $recurId, string $accountId)
    {
        $sql = "SELECT entity_recurrence_id, entity_definition_id, date_processed_to, parent_entity_id,
                    type, interval, date_start,
					date_end, dayofmonth, instance, monthofyear, ep_locked,
					dayofweekmask[1] as day1, dayofweekmask[2] as day2, dayofweekmask[3] as day3,
					dayofweekmask[4] as day4, dayofweekmask[5] as day5, dayofweekmask[6] as day6,
					dayofweekmask[7] as day7
				  FROM " . self::ENTITY_RECUR_TABLE . " WHERE entity_recurrence_id=:entity_recurrence_id";

        $result = $this->getDatabase($accountId)->query($sql, ["entity_recurrence_id" => $recurId]);

        if ($result->rowCount()) {
            $row = $result->fetch();

            $recurrenceData = [
                "entity_recurrence_id" => $row['entity_recurrence_id'],
                "recur_type" => $row['type'],
                "date_processed_to" => $row['date_processed_to'],
                "first_entity_id" => $row['parent_entity_id'],
                "interval" => $row['interval'],
                "date_start" => $row['date_start'],
                "date_end" => $row['date_end'],
                "day_of_month" => $row['dayofmonth'],
                "month_of_year" => $row['monthofyear'],
                "instance" => $row['instance'],
                "ep_locked" => $row['ep_locked'],
                'entity_definition_id' => $row['entity_definition_id'],
            ];

            // Load recurrence rules
            if ($row['entity_definition_id']) {
                $def = $this->entityDefinitionLoader->getById($row['entity_definition_id']);
                if ($this->entityDefinitionLoader->getById($row['entity_definition_id'])) {
                    $recurrenceData['field_date_start'] = $def->recurRules['field_date_start'];
                    $recurrenceData['field_time_start'] = $def->recurRules['field_time_start'];
                    $recurrenceData['field_date_end'] = $def->recurRules['field_date_end'];
                    $recurrenceData['field_time_end'] = $def->recurRules['field_time_end'];
                }
            }

            // Create recurrence pattern to return
            $recurPattern = new RecurrencePattern($accountId);
            $recurPattern->fromArray($recurrenceData);

            // Now set weekday bits
            if ($row['day1']) {
                $recurPattern->setDayOfWeek(RecurrencePattern::WEEKDAY_SUNDAY, true);
            }
            if ($row['day2']) {
                $recurPattern->setDayOfWeek(RecurrencePattern::WEEKDAY_MONDAY, true);
            }
            if ($row['day3']) {
                $recurPattern->setDayOfWeek(RecurrencePattern::WEEKDAY_TUESDAY, true);
            }
            if ($row['day4']) {
                $recurPattern->setDayOfWeek(RecurrencePattern::WEEKDAY_WEDNESDAY, true);
            }
            if ($row['day5']) {
                $recurPattern->setDayOfWeek(RecurrencePattern::WEEKDAY_THURSDAY, true);
            }
            if ($row['day6']) {
                $recurPattern->setDayOfWeek(RecurrencePattern::WEEKDAY_FRIDAY, true);
            }
            if ($row['day7']) {
                $recurPattern->setDayOfWeek(RecurrencePattern::WEEKDAY_SATURDAY, true);
            }

            // Make sure that we start tracking changes from now on
            $recurPattern->resetIsChanged();

            return $recurPattern;
        }

        return null;
    }

    /**
     * Function that will update the parent object id of the recurrence
     *
     * @param string $recurrenceId The id of the recurrence that we will be updating
     * @param string $entityId The id of the entity that we will set as parent object id
     * @param string $accountId Unique id of the account that this pattern belongs to
     * 
     * @return bool
     */
    public function updateParentEntityId(string $recurrenceId, string $entityId, string $accountId)
    {
        if (!$recurrenceId) {
            throw new \InvalidArgumentException("Cannot update recurrence parent object id. Invalid recurrence id.");
        }

        if (!$entityId) {
            throw new \InvalidArgumentException("Cannot update recurrence parent object id. Invalid entity id.");
        }

        $result = $this->getDatabase($accountId)->update(
            self::ENTITY_RECUR_TABLE,
            ["parent_entity_id" => $entityId],
            ["entity_recurrence_id" => $recurrenceId]
        );

        return ($result) ? true : false;
    }

    /**
     * Delete a recurrence pattern
     *
     * @param RecurrencePattern $recurrencePattern
     * @return bool
     */
    public function delete(RecurrencePattern $recurrencePattern)
    {
        if (!$recurrencePattern->getId()) {
            throw new \InvalidArgumentException("You cannot delete a pattern that has not been saved");
        }
        return $this->deleteById($recurrencePattern->getId(), $recurrencePattern->getAccountId());
    }

    /**
     * Delete recurrence pattern by id
     *
     * @param string $recurId The unique id of the recurring pattern to delete
     * @param string $accountId Unique id of the account that this pattern belongs to
     * 
     * @return bool true on success, false on failure
     */
    public function deleteById(string $recurId, string $accountId)
    {

        $result = $this->getDatabase($accountId)->delete(self::ENTITY_RECUR_TABLE, ["entity_recurrence_id" => $recurId]);
        return ($result) ? true : false;
    }

    /**
     * Return the last error that occurred
     *
     * @return Error\Error
     */
    public function getLastError()
    {
        if ($this->lastError) {
            return new Error\Error($this->lastError);
        } else {
            return null;
        }
    }

    /**
     * Select patterns that have not been processed to a specified date
     *
     * This gets a list of pattern IDs that have not been processed to
     * the date specified and date end is after the date specified.
     *
     * @param string $objType The object type to select patterns for
     * @param \DateTime $dateTo The date to indicate if a pattern is stale
     * @param string $accountId Unique id of the account that this pattern belongs to
     * 
     * @return array of IDs of stale patterns
     */
    public function getStalePatternIds(string $objType, \DateTime $dateTo, string $accountId)
    {
        $ret = [];

        $def = $this->entityDefinitionLoader->get($objType);
        $dateToString = $dateTo->format("Y-m-d");

        $sql = "SELECT entity_recurrence_id FROM " . self::ENTITY_RECUR_TABLE . "
				  WHERE f_active is true AND
				  date_processed_to<:date_to_string
				  AND (date_end is null or date_end>=:date_to_string)
                  AND entity_definition_id=:entity_definition_id
                  AND account_id=:account_id";

        $result = $this->getDatabase($accountId)->query($sql, [
            "date_to_string" => $dateToString,
            "entity_definition_id" => $def->getEntityDefinitionId(),
            'account_id' => $accountId,
        ]);

        if ($result->rowCount()) {
            foreach ($result->fetchAll() as $row) {
                $ret[] = $row["entity_recurrence_id"];
            }
        }

        return $ret;
    }
}
