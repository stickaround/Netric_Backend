<?php
/**
 * Handle a series of recurring entities based on a recurrence pattern
 *
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2014-2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\Entity;

/**
 * Class creates entities from a RecurrencePattern
 */
class EntitySeriesWriter
{
	/**
	 * Recurrence Pattern Data Mapper
	 *
	 * @var RecurrenceDataMapper
	 */
	private $recurDataMapper = null;

	/**
	 * Identity mapper for loading/saving/caching recurrence patterns
	 *
	 * @var RecurrenceIdentityMapper
	 */
	private $recurIdentityMapper = null;

	/**
	 * Setup the class
	 *
	 * @param RecurrenceDataMapper $recurrenceDataMapper
	 * @param RecurrenceIdentityMapper $identityMapper For loading/saving/caching patterns
	 */
	public function __construct(RecurrenceDataMapper $recurrenceDataMapper, RecurrenceIdentityMapper $identityMapper)
	{
		$this->recurDataMapper = $recurrenceDataMapper;
		$this->recurIdentityMapper = $identityMapper;
	}

	/**
	 * Create all entities in a recurrence pattern up to a specified date
	 *
	 * @param RecurrencePattern $pattern
	 * @param \DateTime $toDate
	 * @return int number of entities created
	 */
	public function createInstances(RecurrencePattern $pattern, \DateTime $toDate)
	{
		// Make sure we are working with a valid pattern
		if (!$pattern->validatePattern())
			return 0;

		// Make sure we are not locked by another process within the last 2 minutes
		// so we don't duplicate recurring events
		if ($pattern->isSeriesLocked())
			return 0;

		// Lock this pattern to prevent overlap
		$pattern->setSeriesLocked(true);
		$this->recurIdentityMapper->save($pattern);

        // Record the number of entities created
		$numCreated = 0;

        // Get the very next date from the pattern picking up from where it last processed to
		$nextDate  = $pattern->getNextStart();
		if (!$nextDate)
			return 0;

        // Loop through $pattern->getNextStart() until we have reached $toDate
		while($nextDate<=$toDate)
		{

			$numCreated++;

            // Get the next date to process next time around or exit if we've reached the end
			$nextDate = $pattern->getNextStart();
            if (!$nextDate)
                break;
		}

		$pattern->setDateProcessedTo(new \DateTime(date("Y-m-d", $toDate)));
		$pattern->setSeriesLocked(false);
		$this->recurIdentityMapper->save($pattern);

		return $numCreated;
	}
	

	/**************************************************************************
	*	Function: 	removeSeries
	*
	*	Purpose: 	Delete all objects in this series
	***************************************************************************/
	function removeSeries($refObj=null)
	{
		if (!$this->id || !$this->object_type)
			return false;

		if ($refObj)
			$objDef = $refObj;
		else
			$objDef = new CAntObject($this->dbh, $this->object_type);

		// Delete all objects in the series
		$objList = new CAntObjectList($this->dbh, $this->object_type);
		$objList->addCondition("and", $objDef->def->recurRules['field_recur_id'], "is_equal", $this->id);
		if ($objDef->id)
			$objList->addCondition("and", "id", "is_not_equal", $objDef->id);
		$objList->getObjects();
		for ($i = 0; $i < $objList->getNumObjects(); $i++)
		{
			$obj = $objList->getObject($i);
			$obj->recurrenceException = true; // Prevent loops
			$obj->remove(); // series of objects
		}
	}

    private function seriesCreateInstance(RecurrencePattern $recurrencePattern, \DateTime $date)
    {
        $objOrig = new CAntObject($dbh, $this->object_type, $this->parentId);
        $user = ($objOrig->owner_id!=null) ? new AntUser($dbh, $objOrig->owner_id) :  null;
        $objNew = new CAntObject($dbh, $this->object_type, NULL, $user);

        // Set start date and time
        $date_start = $nextDate;
        $time_start = $this->timeStart;

        if ($this->fieldTimeStart)
        {
            // Get time from time_start timestamp
            if (!$time_start && $objOrig->getValue($this->fieldTimeStart))
            {
                if (@strtotime($objOrig->getValue($this->fieldTimeStart))!== false)
                {
                    $time_start = date("h:i A", strtotime($objOrig->getValue($this->fieldTimeStart)));
                }
            }

            if ($this->fieldTimeStart == $this->fieldDateStart)
            {
                $objNew->setValue($this->fieldDateStart, $date_start." ".$time_start);
            }
            else
            {
                $objNew->setValue($this->fieldDateStart, $date_start);
                $objNew->setValue($this->fieldTimeStart, $time_start);
            }
        }
        else
        {
            $objNew->setValue($this->fieldDateStart, $date_start);
        }

        // Set end date and time
        $date_end = $nextDate;
        $time_end = $this->timeEnd;
        if ($this->fieldTimeEnd)
        {
            // Get time from time_end timestamp
            if (!$time_end && $objOrig->getValue($this->fieldTimeEnd))
            {
                if (@strtotime($objOrig->getValue($this->fieldTimeEnd))!== false)
                {
                    $time_end = date("h:i A", strtotime($objOrig->getValue($this->fieldTimeEnd)));
                }
            }

            if ($this->fieldTimeEnd == $this->fieldDateEnd)
            {
                $objNew->setValue($this->fieldDateEnd, $date_end." ".$time_end);
            }
            else
            {
                $objNew->setValue($this->fieldDateEnd, $date_start);
                $objNew->setValue($this->fieldTimeEnd, $time_end);
            }
        }
        else
        {
            $objNew->setValue($this->fieldDateEnd, $date_end);
        }

        // Copy remaining fields
        // ---------------------------------------------------------------
        $all_fields = $objOrig->def->getFields();
        foreach ($all_fields as $fname=>$fdef)
        {
            if ($fname!=$this->fieldDateStart && $fname!=$this->fieldDateEnd
                && $fname!=$this->fieldTimeStart && $fname!=$this->fieldTimeEnd
                && ($fdef->readonly!=true || $fname=='associations') // Copy associations
                && $fname!='activity') // Do not copy activity
            {
                if ($fdef->type == "fkey_multi")
                {
                    $vals = $objOrig->getValue($fname);
                    if (is_array($vals) && count($vals))
                    {
                        foreach ($vals as $val)
                        {
                            $objNew->setMValue($fname, $val);
                        }
                    }
                }
                else
                {
                    $objNew->setValue($fname, $objOrig->getValue($fname));
                }
            }
        }

        // Set recurrence field (read only)
        $objNew->setValue($objNew->fields->recurRules['field_recur_id'], $this->id);
        $oid = $objNew->save();
    }
}