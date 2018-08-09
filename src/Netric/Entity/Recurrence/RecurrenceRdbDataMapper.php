<?php
namespace Netric\Entity\Recurrence;

use Netric\Db;
use Netric\EntityDefinition\EntityDefinitionLoader;
use Netric\Db\Relational\RelationalDbInterface;
use Netric\Error;
use Netric\DataMapperAbstract;

/**
 * Relational Database DataMapper for recurrence pattern
 */
class RecurrenceRdbDataMapper extends DataMapperAbstract
{
    /**
     * Handle to database
     *
     * @var RelationalDbInterface
     */
    private $database = null;

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
     * Class constructor to set up dependencies
     *
     * @param RelationalDbInterface $database Handles to database actions
     * @param EntityDefinitionLoader $entityDefinitionLoader Used to get the id of objType
     */
    public function __construct(RelationalDbInterface $database, EntityDefinitionLoader $entityDefinitionLoader)
    {
        $this->database = $database;
        $this->entityDefinitionLoader = $entityDefinitionLoader;
    }

    /**
     * Save a recurrence pattern to the database
     *
     * When the pattern is saved for the first time, it can use the $useId field
     * to see if it should be using a reserved ID or request a new one. This is
     * sometimes used when we need to save a reference to a recurrence in an entity
     * before saving the details of said recurrence.
     *
     * @param RecurrencePattern $recurPattern
     * @param int $useId We can reserve an ID to use when creating a new instace via getNextId()
     * @return int Unique id of the pattern on success or null on failure this $this->lastError set
     * @throws \InvalidArgumentException in the instance that the pattern is not valid
     * @throws \RuntimeException if saving failed for some reason
     */
    public function save(RecurrencePattern $recurPattern, $useId = null)
    {
        if (!$recurPattern->validatePattern()) {
            throw new \InvalidArgumentException($recurPattern->getLastError()->getMessage());
        }

        $data = $recurPattern->toArray();
        $dayOfWeekMask = $recurPattern->getDayOfWeekMask();

        if (!$data['obj_type']) {
            throw new \InvalidArgumentException("No object type set for recurring pattern");
        }

        // Get object type id
        $def = $this->entityDefinitionLoader->get($data['obj_type']);

        $recurrenceData = [
            'object_type_id' => $def->getId(),
            'object_type' => $data['obj_type'],
            'date_processed_to' => $data['date_processed_to'],
            'parent_object_id' => $data['first_entity_id'],
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

        $recurrenceId = null;
        if ($recurPattern->getId()) {
            $recurrenceId = $recurPattern->getId();
            $sql = "SELECT id FROM object_recurrence WHERE id=:id";
            $result = $this->database->query($sql, ["id" => $recurrenceId]);

            if ($result->rowCount()) {
                /*
                 * It is possible that the id of the recurrence pattern was pre-set with the
                 * next unique id prior to it having been saved in the database. If this is the
                 * case we will need to make sure we include the id in the insert statement
                 * because we cannot ever assume that if it already has an id that it was previously saved.
                 */
                $recurrenceData['id'] = $recurrenceId;

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

                $sql = "UPDATE object_recurrence SET ";
                $sql .= implode(',', $updateStatements);
                $sql .= " WHERE id=:cond_id";

                // Run the update and return the id as the result
                $updateParams["cond_id"] = $recurrenceId;
                $result = $this->database->query($sql, $updateParams);

                return $recurrenceId;
            }
        }

        // Override the recurrence id if the $useId is set
        if ($useId) {
            $recurrenceData["id"] = $useId;
            $recurrenceId = $useId;
        }

        $insertColumns = array_keys($recurrenceData);
        $insertParams = $insertColumns;
        foreach ($daysOfWeekMaskData as $index => $value) {
            $insertColumns[] = "dayofweekmask[$index]";
            $insertParams[] = "dayofweekmask_$index";
            $recurrenceData["dayofweekmask_$index"] = ($value > 0) ? true : false;
        }

        $sql = "INSERT INTO object_recurrence (" . implode(",", $insertColumns) . ")";
        // Add values as params by prefixing each with ':'
        $sql .= " VALUES(:" . implode(",:", $insertParams) . ")";

        // Run query, get next value (if selected), and commit
        $this->database->query($sql, $recurrenceData);

        // Get the last inserted id in object_recurrence table
        $recurrenceId = $this->database->getLastInsertId(null, "object_recurrence");
        $recurPattern->setId($recurrenceId);

        return $recurrenceId;
    }

    /**
     * Secure a unique id to use before it is saved
     *
     * @return int|bool false if fails
     */
    public function getNextId()
    {
        $sql = "select nextval('object_recurrence_id_seq') as id";
        $result = $this->database->query($sql);

        if ($result->rowCount()) {
            $row = $result->fetch();
            return $row["id"];
        }

        return false;
    }

    /**
     * Load up an entity recurrence pattern by id
     *
     * @param string $recurId The unique id of the pattern to load
     * @return RecurrencePattern
     * @throws \InvalidArgumentException if the id passed is not a valid number
     */
    public function load($recurId)
    {
        $sql = "SELECT id, object_type_id, object_type, date_processed_to, parent_object_id,
                    type, interval, date_start,
					date_end, dayofmonth, instance, monthofyear, ep_locked,
					dayofweekmask[1] as day1, dayofweekmask[2] as day2, dayofweekmask[3] as day3,
					dayofweekmask[4] as day4, dayofweekmask[5] as day5, dayofweekmask[6] as day6,
					dayofweekmask[7] as day7
				  FROM object_recurrence WHERE id=:id";

        $result = $this->database->query($sql, ["id" => $recurId]);

        if ($result->rowCount()) {
            $row = $result->fetch();

            $recurrenceData = [
                "id" => $row['id'],
                "recur_type" => $row['type'],
                "obj_type" => $row['object_type'],
                "date_processed_to" => $row['date_processed_to'],
                "first_entity_id" => $row['parent_object_id'],
                "interval" => $row['interval'],
                "date_start" => $row['date_start'],
                "date_end" => $row['date_end'],
                "day_of_month" => $row['dayofmonth'],
                "month_of_year" => $row['monthofyear'],
                "instance" => $row['instance'],
                "ep_locked" => $row['ep_locked'],
            ];

            // Load recurrence rules
            if ($row['object_type']) {
                $def = $this->entityDefinitionLoader->get($row['object_type']);
                $recurrenceData['field_date_start'] = $def->recurRules['field_date_start'];
                $recurrenceData['field_time_start'] = $def->recurRules['field_time_start'];
                $recurrenceData['field_date_end'] = $def->recurRules['field_date_end'];
                $recurrenceData['field_time_end'] = $def->recurRules['field_time_end'];
            }

            // Create recurrence pattern to return
            $recurPattern = new RecurrencePattern();
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
     * @param int $recurrenceId The id of the recurrence that we will be updating
     * @param int $entityId The id of the entity that we will set as parent object id
     * @return bool
     */
    public function updateParentObjectId(int $recurrenceId, int $entityId)
    {
        if (!$recurrenceId) {
            throw new \InvalidArgumentException("Cannot update recurrence parent object id. Invalid recurrence id.");
        }

        if (!$entityId) {
            throw new \InvalidArgumentException("Cannot update recurrence parent object id. Invalid entity id.");
        }

        $result = $this->database->update("object_recurrence",
            ["parent_object_id" => $entityId],
            ["id" => $recurrenceId]);

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
        return $this->deleteById($recurrencePattern->getId());
    }

    /**
     * Delete recurrence pattern by id
     *
     * @param int $recurId The unique id of the recurring pattern to delete
     * @return bool true on success, false on failure
     */
    public function deleteById($recurId)
    {

        $result = $this->database->delete("object_recurrence", ["id" => $recurId]);
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
     * @return array of IDs of stale patterns
     */
    public function getStalePatternIds($objType, \DateTime $dateTo)
    {
        $ret = [];

        $def = $this->entityDefinitionLoader->get($objType);
        $dateToString = $dateTo->format("Y-m-d");

        $sql = "SELECT id FROM object_recurrence
				  WHERE f_active is true AND
				  date_processed_to<:date_to_string
				  AND (date_end is null or date_end>=:date_to_string)
				  AND object_type_id=:object_type_id";

        $result = $this->database->query($sql, [
            "date_to_string" => $dateToString,
            "object_type_id" => $def->getId()
        ]);

        if ($result->rowCount()) {
            foreach ($result->fetchAll() as $row) {
                $ret[] = $row["id"];
            }
        }

        return $ret;
    }
}
