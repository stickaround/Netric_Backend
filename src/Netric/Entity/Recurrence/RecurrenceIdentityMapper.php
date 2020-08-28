<?php

/**
 * IdentityMapper for recurrence patterns
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\Recurrence;

use Netric\Entity\Entity;

class RecurrenceIdentityMapper
{
    /**
     * Recurrence Pattern Data Mapper
     *
     * @var RecurrenceDataMapper
     */
    private $recurDataMapper = null;

    /**
     * Cache loaded recurrence patterns
     *
     * @var array
     */
    private $cachedPatterns = [];

    /**
     * Construct the identity mapper and set all dependencies
     *
     * @param RecurrenceDataMapper $recurrenceDataMapper To save and load patterns from the datastore
     */
    public function __construct(RecurrenceRdbDataMapper $recurrenceDataMapper)
    {
        $this->recurDataMapper = $recurrenceDataMapper;
    }

    /**
     * Save a recurrence pattern to the database
     *
     * When the pattern is saved for the first time, it can use the $useId field
     * to see if it should be using a reserved ID or request a new one. This is
     * sometimes used when we need to save a reference to a recurrence in an entity
     * before saving the details of said recurrence.
     *
     * @param \Netric\Entity\Recurrence\RecurrencePattern $recurPattern
     * @param string $useId We can reserve an ID to use when creating a new instace via getNextId()
     * @return string Unique id of the pattern on success or null on failure this $this->lastError set
     */
    public function save(RecurrencePattern $recurPattern, $useId = null)
    {
        return $this->recurDataMapper->save($recurPattern, $useId);
    }

    /**
     * Load up an entity recurrence pattern by id
     *
     * @param id $id The unique id of the pattern to load
     * @param string $accountId The accountId of the pattern we are going to load
     * 
     * @return RecurrencePattern
     */
    public function getById($id, string $accountId)
    {
        // First check to see if the pattern was already loaded and cached
        $pattern = $this->getLoadedPattern($id);

        // If we have not yet loaded it then load from db and cache locally
        if (!$pattern) {
            $pattern = $this->recurDataMapper->load($id, $accountId);
            if ($pattern) {
                $this->cachedPatterns[$id] = $pattern;
            }
        }

        return $pattern;
    }

    /**
     * Save a recurrence pattern from an entity
     *
     * @param Entity $entity The entity containing the recurrence pattern to save
     * @param int $useId We can reserve an ID to use when creating a new instace via getNextId()
     * @return bool
     * @throws \RuntimeException if there is a problem saving
     */
    function saveFromEntity(Entity $entity, $useId = null)
    {
        $recurPattern = $entity->getRecurrencePattern();
        $def = $entity->getDefinition();

        if ($entity->getEntityId() && $recurPattern) {
            // Move first entity to current entity
            if ($recurPattern->getFirstEntityId() != $entity->getEntityId()) {
                $recurPattern->setFirstEntityId($entity->getEntityId());
            }

            // Make sure the object type is correct for validation of fields
            if ($recurPattern->getObjTypeId() != $def->getEntityDefinitionId()) {
                $recurPattern->setObjTypeId($def->getEntityDefinitionId());
            }

            // Get the start date which is required for all recurring patterns
            $curStart = $entity->getValue($def->recurRules['field_date_start']);

            // Epic fail! A start_field value of the entity is required for recurrence
            if (!$curStart) {
                throw new \RuntimeException($def->recurRules['field_date_start'] . " is required for saving recurrence");
            };

            // Set the last date the recurrence pattern was processed to
            $recurPattern->setDateProcessedTo(new \DateTime(date("Y-m-d", $curStart)));

            // Make sure this succeeds, it should never fail
            if ($this->save($recurPattern, $useId)) {
                return true;
            } else {
                throw new \RuntimeException($this->recurDataMapper->getLastError()->getMessage());
            }
        }

        return false;
    }

    /**
     * Delete a recurrence pattern
     *
     * @param RecurrencePattern $recurrencePattern
     * @return bool
     */
    public function delete(RecurrencePattern $recurrencePattern)
    {
        $toDelete = $recurrencePattern->getId();
        if ($this->recurDataMapper->delete($recurrencePattern)) {
            unset($this->cachedPatterns[$toDelete]);
            return true;
        }

        return false;
    }

    /**
     * Secure a unique id to use before it is saved
     *
     * @return int|bool false if fails
     */
    public function getNextId()
    {
        return $this->recurDataMapper->getNextId();
    }

    /**
     * Select patterns that have not been processed to a specified date
     *
     * @param string $objType The object type to select patterns for
     * @param \DateTime $dateTo The date to indicate if a pattern is stale
     * @param string $accountId Unique id of the account that this pattern belongs to
     * 
     * @return array RecurrencePattern[]
     */
    public function getStalePatterns(string $objType, \DateTime $dateTo, string $accountId)
    {
        $recurrencePatterns = [];
        $stalePatternIds = $this->recurDataMapper->getStalePatternIds($objType, $dateTo, $accountId);

        // Load each through this identity mapper - which caches them - and return
        foreach ($stalePatternIds as $pid) {
            $recurrencePatterns[] = $this->getById($pid, $accountId);
        }

        return $recurrencePatterns;
    }

    /**
     * Get recurring pattern if loaded locally
     *
     * @param int $id The unique id of the pattern to load
     * @return RecurrencePattern
     */
    private function getLoadedPattern($id)
    {
        if (isset($this->cachedPatterns[$id])) {
            return $this->cachedPatterns[$id];
        } else {
            return null;
        }
    }
}
