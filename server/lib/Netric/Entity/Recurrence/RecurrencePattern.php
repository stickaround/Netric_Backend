<?php
/**
 * Generic recurrence pattern used with entities and various other objects in netric
 *
 *  @author Sky Stebnicki <sky.stebnicki@aereus.com>
 *  @copyright 2014-2015 Aereus
 */
namespace Netric\Entity\Recurrence;

/**
 * Pattern used to recur entities
 */
class RecurrencePattern
{
	/**
	 * Type of recurrence applied to this pattern
	 *
	 * @var const self::RECUR_*
	 */
	private $recurType = null;
	const RECUR_DAILY = 1;
	const RECUR_WEEKLY = 2;
	const RECUR_MONTHLY = 3;
	const RECUR_MONTHNTH = 4;
	const RECUR_YEARLY = 5;
	const RECUR_YEARNTH = 6;

	/**
	 * Inverval every (num) days/weeks/months/years - defaults to 1
	 * 
	 * @var int
	 */
	private $interval = 1;

	/**
	 * When the recurrence starts
	 *
	 * @var \DateTime
	 */
	private $dateStart = null;

	/**
	 * When the recurrence ends, if null then no end
	 *
	 * @var \DateTime
	 */
	private $dateEnd = null;

	/**
	 * All day event flag
	 *
	 * @var bool
	 */
	private $fAllDay = false;

	/**
	 * If type is RECUR_MONTHLY or RECUR_YEARLY dayOfMonth can be set
	 *
	 * @var int 1-31
	 */
	private $dayOfMonth = null;

	/**
	 * Recor on a specific month of the year if type is yearly or yearnth
	 * 
	 * @var int 1-12
	 */
	private $monthOfYear = null;

	/**
	 * Bitmask used to turn on and off days of the week
	 *
	 * @var int bitmask of self::WEEKDAY_*
	 */
	private $dayOfWeekMask = null;
	const WEEKDAY_SUNDAY = 1;
	const WEEKDAY_MONDAY = 2;
	const WEEKDAY_TUESDAY = 4;
	const WEEKDAY_WEDNESDAY = 8;
	const WEEKDAY_THURSDAY = 16;
	const WEEKDAY_FRIDAY = 32;
	const WEEKDAY_SATURDAY = 64;

	/**
	 * The duration of the pattern in minutes
	 * 
	 * @var int
	 */
	private $duration = null;

	/**
	 * The 1st-5th(last) RECUR_MONTHNTH and RECUR_YEARNTH
	 *
	 * Instance of a weekday within in month such as 1st Sunday or last thirsday.
	 *
	 * @var int 1-5
	 */
	private $instance = null;
	const NTH_1ST = 1;
	const NTH_2ND = 2;
	const NTH_3RD = 3;
	const NTH_4TH = 4;
	const NTH_LAST = 5;

	/**
	 * Recurrance pattern is active or archived
	 *
	 * @var bool
	 */
	private $fActive = true;

	/**
	 * The object type id that is associated with this recurrence pattern
	 *
	 * @var int unique id of EntityDefinition
	 */
	private $object_type_id = null;

	/**
	 * Name of the object type assoicated with this recurrence
	 *
	 * @var string Name of EntityDefintion
	 */
	private $object_type = 0;

	/**
	 * ID of originating object - orginal event or task where recur was started
	 * 
	 * @var int
	 */
	private $parentId = null;

	//var $calendarId;		// (optional) calnedar id of this recurrence instance

	/**
	 * The last date this recurrence processsed to.
	 *
	 * Useful for write-ahead processing of instance creation like when viewing
	 * calendar months the code can look ahead +1 month to process recurrence
	 * if needed.
	 *
	 * @var \DateTime
	 */
	private $dateProcessedTo = null;

	/**
	 * The unique id of this recurrence pattern
	 *
	 * @var int
	 */
	private $id = null;

	/**
	 * We can reserve an ID to use when creating a new instace via getNextId()
	 *
	 * When the pattern is saved for the first time, it will check this field
	 * to see if it should be using a reserved ID or request a new one. This is
	 * sometimes used when we need to save a reference to a recurrence in an entity
	 * before saving the details of said recurrence.
	 *
	 * @var int
	 */
	private $useId = null;

	/**
	 * The entity field name used set the start for each instance
	 * 
	 * This is required for all recurring entities.
	 *
	 * @var string
	 */
	private $fieldDateStart = null;

	/**
	 * Optional field name used to trigger the start time for each instance
	 *
	 * If blank then we only care about the date and not the time.
	 *
	 * @var string
	 */
	private $fieldTimeStart = null;


	/**
	 * Entity field name used to set the end date for each instance
	 *
	 * This is required for all recurring entities.
	 *
	 * @var string
	 */
	private $fieldDateEnd = null;

	/**
	 * Optional fiele name used to trigger the end time for each instance
	 *
	 * @var string
	 */
	private $fieldTimeEnd = null;

	/**
	 * Changelog used to track when params for this entity have changed
	 *
	 * @var array
	 */
	private $arrChangeLog = array();

	/**
	 * Locked timestamp
	 *
	 * @var int (epoch)
	 */
	public $epLocked = 0;

	/**
	 * Set the recurrence type
	 *
	 * @param int const self::RECUR_*
	 */
	public function setRecurType($typeId)
	{
		$this->recurType = $typeId;
	}

	/**
	 * Set the interval
	 *
	 * @param int $interval Every n number of days/weeks/months/years
	 */
	public function setInterval($interval)
	{
		$this->interval = $interval;
	}

	/**
	 * Set the start date
	 *
	 * @param \DateTime $dateStart
	 */
	public function setDateStart(\DateTime $dateStart)
	{
		$this->dateStart = $dateStart;
	}

	/**
	 * Set the end date
	 *
	 * @param \DateTime $dateEnd
	 */
	public function setDateEnd(\DateTime $dateEnd)
	{
		$this->dateEnd = $dateEnd;
	}

	/**
	 * Update the processed to date
	 *
	 * @param \DateTime $dateProcessedTo
	 */
	public function setDateProcessedTo(\DateTime $dateProcessedTo)
	{
		$this->dateProcessedTo = $dateProcessedTo;
	}

	/**
	 * Set day of week on or off
	 *
	 * @param int $day Day from self::WEEKDAY_*
	 * @param bool $on If true the day bit is on, if false then unset the bit
	 */
	public function setDayOfWeek($day, $on=true)
	{
		if ($on)
		{
			$this->dayOfWeekMask = $this->dayOfWeekMask | $day;
		}
		else
		{
			$this->dayOfWeekMask = $this->dayOfWeekMask & ~$day;
		}
	}

	/**
	 * Set the month of year for yearly and yearnth
	 *
	 * @param int $monthOfYear
	 */
	public function setMonthOfYear($monthOfYear)
	{
		$this->monthOfYear = $monthOfYear;
	}

	/**
	 * Set the day of month to recur on
	 *
	 * @param int $dayOfMonth
	 */
	public function setDayOfMonth($dayOfMonth)
	{
		$this->dayOfMonth = $dayOfMonth;
	}

	/**
	 * Set the nth instance for all nth type recurring patterns
	 *
	 * @param int $dayOfMonth
	 */
	public function setInstance($instance)
	{
		$this->instance = $instance;
	}

	/**
	 * Get the day of week bitmask
	 *
	 * @return int
	 */
	public function getDayOfWeekMask()
	{
		return $this->dayOfWeekMask;
	}

	/**
	 * Check to see if values have changed since the last save
	 *
	 * @return bool true if changed
	 */
	public function isChanged()
	{
		if (count($this->arrChangeLog) == 0) // Check for new
		{
			return true;
		}

		// Now check previously set values to see if this has changed
		if ($this->arrChangeLog['interval'] != $this->interval)
			return true;
		else if ($this->arrChangeLog['type'] != $this->recurType)
			return true;
		else if ($this->arrChangeLog['date_start'] != $this->dateStart)
			return true;
		else if ($this->arrChangeLog['date_end'] != $this->dateEnd)
			return true;
		else if ($this->arrChangeLog['dayofmonth'] != $this->dayOfMonth)
			return true;
		else if ($this->arrChangeLog['monthofyear'] != $this->monthOfYear)
			return true;
		else if ($this->arrChangeLog['instance'] != $this->instance)
			return true;
		else if ($this->arrChangeLog['day1']=='t' && !($this->dayOfWeekMask & WEEKDAY_SUNDAY))
			return true;
		else if ($this->arrChangeLog['day1']=='f' && ($this->dayOfWeekMask & WEEKDAY_SUNDAY))
			return true;
		else if ($this->arrChangeLog['day2']=='t' && !($this->dayOfWeekMask & WEEKDAY_MONDAY))
			return true;
		else if ($this->arrChangeLog['day2']=='f' && ($this->dayOfWeekMask & WEEKDAY_MONDAY))
			return true;
		else if ($this->arrChangeLog['day3']=='t' && !($this->dayOfWeekMask & WEEKDAY_TUESDAY))
			return true;
		else if ($this->arrChangeLog['day3']=='f' && ($this->dayOfWeekMask & WEEKDAY_TUESDAY))
			return true;
		else if ($this->arrChangeLog['day4']=='t' && !($this->dayOfWeekMask & WEEKDAY_WEDNESDAY))
			return true;
		else if ($this->arrChangeLog['day4']=='f' && ($this->dayOfWeekMask & WEEKDAY_WEDNESDAY))
			return true;
		else if ($this->arrChangeLog['day5']=='t' && !($this->dayOfWeekMask & WEEKDAY_THURSDAY))
			return true;
		else if ($this->arrChangeLog['day5']=='f' && ($this->dayOfWeekMask & WEEKDAY_THURSDAY))
			return true;
		else if ($this->arrChangeLog['day6']=='t' && !($this->dayOfWeekMask & WEEKDAY_FRIDAY))
			return true;
		else if ($this->arrChangeLog['day6']=='f' && ($this->dayOfWeekMask & WEEKDAY_FRIDAY))
			return true;
		else if ($this->arrChangeLog['day7']=='t' && !($this->dayOfWeekMask & WEEKDAY_SATURDAY))
			return true;
		else if ($this->arrChangeLog['day7']=='f' && ($this->dayOfWeekMask & WEEKDAY_SATURDAY))
			return true;

		return false;
	}

	
	/**
	 * Get the DateTime for the next instance start date
	 *
	 * @return \DateTime
	 */
	public function getNextStart()
	{
		switch ($this->recurType)
		{
		case self::RECUR_DAILY:
			return $this->getNextStartDaily();
			break;
		case self::RECUR_WEEKLY:
			return $this->getNextStartWeekly();
			break;
		case self::RECUR_MONTHLY:
			return $this->getNextStartMonthly();
			break;
		case self::RECUR_MONTHNTH:
			return $this->getNextStartMonthlyNth();
			break;
		case self::RECUR_YEARLY:
			return $this->getNextStartYearly();
			break;
		case self::RECUR_YEARNTH:
			return $this->getNextStartYearlyNth();
			break;
		}

		return false; // RecurrenceType was not set for this object
	}

	/**
	 * Step through to the next start date for daily recurrance
	 * 
	 * @return \DateTime
	 */
	private function getNextStartDaily()
	{
		if (!$this->dateStart || !$this->interval)
			return false;

		// If this is the first time called then just return the start date
		if (!$this->dateProcessedTo)
		{
			$this->dateProcessedTo = $this->dateStart;
			return $this->dateProcessedTo;
		}

		$dtCur = clone $this->dateProcessedTo;

		// Step over dtCur if not beginning
		$tsTmp = $this->dateStart;
		while ($tsTmp <= $dtCur)
		{
			$tsTmp->add(new \DateInterval('P' . $this->interval . 'D'));
		}
		$dtCur = $tsTmp;

		if ($this->dateEnd)
		{
			if ($dtCur > $this->dateEnd)
			{
				$this->dateProcessedTo = $dtCur;
				$dtCur = false;
			}
		}

		if ($dtCur)
		{
			$this->dateProcessedTo = $dtCur;
			return $dtCur;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Step through to the next start date for weekly recurrence
	 *
	 * @return \DateTime
	 */
	private function getNextStartWeekly()
	{
		if (!$this->dayOfWeekMask || !$this->dateStart || !$this->interval)
			return false;

		$maxloops = 100000; // Prevent infiniate loops
		$ret = null;

		if ($this->dateProcessedTo)
		{
			$dtCur = clone $this->dateProcessedTo;
			// Step over the last date we processed
			$dtCur->add(new \DateInterval('P1D'));
		}
		else
		{
			$dtCur = clone $this->dateStart;
		}
		
		$loops = 0;
		do
		{
			// Step through the week and look for a match
			$tsTmp = clone $dtCur;
			$dow = $dtCur->format("w"); // get starting day of week - 0 for Sunday, 6 for Saturday
			for ($i = (int)$dow; $i<=6; $i++) // Loop while we don't have a match and we are still within the week
			{
				switch ($i)
				{
				case 0:
					$test = self::WEEKDAY_SUNDAY;
					break;
				case 1:
					$test = self::WEEKDAY_MONDAY;
					break;
				case 2:
					$test = self::WEEKDAY_TUESDAY;
					break;
				case 3:
					$test = self::WEEKDAY_WEDNESDAY;
					break;
				case 4:
					$test = self::WEEKDAY_THURSDAY;
					break;
				case 5:
					$test = self::WEEKDAY_FRIDAY;
					break;
				case 6:
					$test = self::WEEKDAY_SATURDAY;
					break;
				}

				// Check bitmask for a match
				if ($this->dayOfWeekMask & $test)
				{
					$ret = $tsTmp;
					break;
				}
				else
				{
					// Add a day and cotinue to loop through the rest of the week
					$tsTmp->add(new \DateInterval('P1D'));
				}
			}

			// If nothing was found at the end of the week then skip $this->interval weeks
			if (!$ret)
			{
				// Increment week
				$dtCur->add(new \DateInterval('P' . $this->interval . 'W'));

				// rewined to beginning of next week - Sunday (0)
				$dow = $dtCur->format("w"); // day of week - 0 for Sunday, 6 for Saturday
				if ($dow)
					$dtCur->sub(new \DateInterval('P' . $dow . 'D'));
			}

			// Increment safty check
			$loops++;

		} while(!$ret && $loops<$maxloops);

		// Check if we have moved beyond the end of this pattern
		if ($this->dateEnd)
		{
			if ($ret > $this->dateEnd)
			{
				$this->dateProcessedTo = clone $ret;
				$ret = false;
			}
		}

		// Return our findings
		if ($ret)
		{
			$this->dateProcessedTo = clone $ret;
			return $ret;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Step through to the next start date for monthly recurrence
	 *
	 * @return \DateTime
	 */
	private function getNextStartMonthly()
	{
		if (!$this->dateStart)
			throw new \InvalidParamsException("dateStart must be set first");

		// Check if this is the first time we have processed this recurrence
		if (!$this->dateProcessedTo)
		{
			$tsStart = clone $this->dateStart;

			// Deal will a different start date from the dayOfMonth
			if ($this->dayOfMonth != (int)$tsStart->format("j"))
			{
				// Rewind to beginning of the month
				$tsStart = new \DateTime($tsStart->format("Y") . "-" . $tsStart->format("m") . "-01");
				// Add a month - no longer needed
				//$tsStart->add(new \DateInterval('P1M'));
				// 't' returns the number of days in current month
				$lastDayOfMonth = (int)$tsStart->format('t'); 

				// If not a valid date then skip to next month
				if ($lastDayOfMonth >= $this->dayOfMonth) 
				{
					$ret = new \DateTime();
					$ret->setDate($tsStart->format("Y"), $tsStart->format("m"), $this->dayOfMonth);
					$ret->setTime(0, 0);
				}
				else
				{				
					/// Jump over to next month because $dayOfMonth does not exist in this month
					$this->dateProcessedTo = new \DateTime($tsStart->format("Y") . "-" . $tsStart->format("m") . "-" . $lastDayOfMonth);
					$ret = $this->getNextStartMonthly();
				}
			}
			else
			{
				$ret = clone $this->dateStart;
			}

			$this->dateProcessedTo = clone $ret;
			return $ret;
		}

		$tsBegin = clone $this->dateStart;
		$tsCur = clone $this->dateProcessedTo;

		// Step over tsCur if not beginning
		$tsTmp = $tsBegin;
		while ($tsTmp <= $tsCur)
		{
			// Rewind to beginning of the month
			$nextMonth = new \DateTime();
			// Set date
			$nextMonth->setDate($tsTmp->format("Y"), $tsTmp->format("m"), 1);
			$nextMonth->setTime(0, 0);
			// Add a month
			$nextMonth->add(new \DateInterval('P' . $this->interval . 'M'));
			// Get the last day of next month
			$lastDayOfMonth = (int)$nextMonth->format('t');

			// If not a valid date then skip to next month
			if ($lastDayOfMonth >= $this->dayOfMonth) 
			{
				$tsTmp = new \DateTime();
				$tsTmp->setDate($nextMonth->format("Y"), $nextMonth->format("m"), $this->dayOfMonth);
				$tsTmp->setTime(0, 0);
			}
			else
			{
				$tsTmp = new \DateTime();
				$tsTmp->setDate($nextMonth->format("Y"), $nextMonth->format("m"), $lastDayOfMonth);
				$tsTmp->setTime(0, 0);

				// Step over this if needed to the next month
				if ($tsTmp > $tsCur) 
					$tsCur = $tsTmp; 
			}

		}
		$tsCur = $tsTmp;

		if ($this->dateEnd)
		{
			if ($tsCur > $this->dateEnd)
			{
				$this->dateProcessedTo = clone $tsCur;
				$tsCur = false;
			}
		}

		if ($tsCur)
		{
			$this->dateProcessedTo = clone $tsCur;
			return $tsCur;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Step through to the next start date for monthlynth recurrence
	 *
	 * @return \DateTime
	 */
	private function getNextStartMonthlyNth()
	{
		$ret = null;

		// Get the current date to begin processing
		$dtBegin = clone $this->dateStart;
		if ($this->dateProcessedTo)
		{
			$dtCur = clone $this->dateProcessedTo;
			$dtCur->add(new \DateInterval('P1D'));
		}
		else
			$dtCur = $dtBegin;
		
		// Step over days in each week until we have a $ret or have exceed $maxloops
		$maxloops = 100; 
		$loops = 0;

		do
		{
			// Step through the month and look for a match
			$tsTmp = clone $dtCur;

			// Get the current day
			$day = (int)$dtCur->format("j"); 

			// Get the last day of the month
			$lastDayOfMonth = (int)$tsTmp->format('t');

			// Loop while we don't have a match and we are still within the month
			for ($i = (int)$day; $i<=(int)$lastDayOfMonth; $i++) 
			{
				// get starting day of week - 0 for Sunday, 6 for Saturday
				$dow = (int) $tsTmp->format("w");

				switch ($dow)
				{
				case 0:
					$test = self::WEEKDAY_SUNDAY;
					break;
				case 1:
					$test = self::WEEKDAY_MONDAY;
					break;
				case 2:
					$test = self::WEEKDAY_TUESDAY;
					break;
				case 3:
					$test = self::WEEKDAY_WEDNESDAY;
					break;
				case 4:
					$test = self::WEEKDAY_THURSDAY;
					break;
				case 5:
					$test = self::WEEKDAY_FRIDAY;
					break;
				case 6:
					$test = self::WEEKDAY_SATURDAY;
					break;
				}

				// 2nd Monday, 1st Tuesday etc...
				$currentInstance = ceil((int)$tsTmp->format('j') / 7);
				// Last thursday etc...
				$f_lastwkdayinmonth = $this->dateIsLastWkDayInMonth($tsTmp);

				// Check if we have a match to return
				if ($this->dayOfWeekMask & $test && ($currentInstance==$this->instance || ($this->instance==5 && $f_lastwkdayinmonth)))
				{
					$ret = $tsTmp;
					break;
				}
				else
				{
					$tsTmp->add(new \DateInterval('P1D'));
				}
			}

			// If no match was found then skip to the beginning of next month
			if (!$ret)
			{
				// Set date to beginning of the month
				$dtCur->setDate($dtCur->format("Y"), $dtCur->format("n"), 1);
				// Add interval months
				$dtCur->add(new \DateInterval('P' . $this->interval . 'M'));
			}

			// Increment counter to keep things safe
			$loops++;

		} while(!$ret && $loops<$maxloops);

		// Check to see if we have moved beyond the bounds of this pattern
		if ($this->dateEnd)
		{
			if ($ret > $this->dateEnd)
			{
				$this->dateProcessedTo = clone $ret;
				$ret = false;
			}
		}

		if ($ret)
		{
			$this->dateProcessedTo = clone $ret;
			return $ret;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Step through to the next start date for yearly recurrence
	 * 
	 * @return \DateTime
	 */
	private function getNextStartYearly()
	{
		if (!$this->monthOfYear || !$this->dayOfMonth || !$this->interval)
			return false;

		if (!$this->dateProcessedTo)
		{
			$ret = clone $this->dateStart;
			$ret->setDate((int)$this->dateStart->format("Y"), $this->monthOfYear, $this->dayOfMonth);
			$ret->setTime(0, 0);
			if ($this->dateStart > $ret)
			{
				$this->dateProcessedTo = clone $this->dateStart;

				// Call myself but this time with dateProcessedTo set
				$ret = $this->getNextStartYearly();
			}
			else
			{
				$this->dateProcessedTo = clone $ret;
			}

			// Easy, we found the first hit this month
			return $ret;
		}

		// Get real start point
		$dtBegin = clone $this->dateStart;
		if ((int)$dtBegin->format("j") != $this->dayOfMonth)
		{
			$tmp = clone $dtBegin;
			$tmp->setDate((int)$dtBegin->format("Y"), $this->monthOfYear, $this->dayOfMonth);
			if ($dtBegin > $tmp)
			{
				// Add a year
				$dtBegin->add(new \DateInterval("P1Y"));
			}
			else
				$dtBegin = $tmp;
		}

		// Step over dtCur if not beginning
		$dtCur = clone $dtBegin;
		while ($dtCur <= $this->dateProcessedTo)
		{
			$dtCur->add(new \DateInterval("P" . $this->interval . "Y"));
		}

		// Check if we have gone beyond the bounds of this pattern (past dateEnd)
		if ($this->dateEnd)
		{
			if ($dtCur > $this->dateEnd)
			{
				$this->dateProcessedTo = clone $dtCur;
				$dtCur = false;
			}
		}

		if ($dtCur)
		{
			$this->dateProcessedTo = clone $dtCur;
			return $dtCur;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Step through to the next start date for yearly recurrence
	 * 
	 * @return \DateTime
	 */
	private function getNextStartYearlyNth()
	{
		if (!$this->monthOfYear || !$this->instance || !$this->interval)
			return false;

		$maxloops = 100000; // Prevent infiniate loops
		$ret = null;

		// Get real start point
		$tsBegin = clone $this->dateStart;
		if ((int)$tsBegin->format("m") != $this->monthOfYear)
		{
			$tmp = clone $tsBegin;
			$tmp->setDate((int)$tsBegin->format("Y"), $this->monthOfYear, 1);
			if ($tsBegin > $tmp)
				$tmp->add(new \DateInterval("P1Y"));
			
			$tsBegin = $tmp;
		}

		// Set current date iterator
		if ($this->dateProcessedTo)
		{
			$tsCur = clone $this->dateProcessedTo;
			$tsCur->add(new \DateInterval("P1Y"));
			// Rewind to beginning of the month
			$tsCur->setDate($tsCur->format("Y"), $tsCur->format("n"), 1);
		}
		else
		{
			$tsCur = $tsBegin;
		}

		$loops = 0;
		do
		{
			// Step through the month and look for a match
			$tsTmp = clone $tsCur;

			// Get the current day
			$day = (int)$tsTmp->format("j"); 

			// Get the last day of the month
			$lastDayOfMonth = (int)$tsTmp->format('t');

			// Loop while we don't have a match and we are still within the month
			for ($i = (int)$day; $i<=(int)$lastDayOfMonth; $i++) 
			{
				$dow = $tsTmp->format("w"); // get starting day of week - 0 for Sunday, 6 for Saturday
				switch ($dow)
				{
				case 0:
					$test = self::WEEKDAY_SUNDAY;
					break;
				case 1:
					$test = self::WEEKDAY_MONDAY;
					break;
				case 2:
					$test = self::WEEKDAY_TUESDAY;
					break;
				case 3:
					$test = self::WEEKDAY_WEDNESDAY;
					break;
				case 4:
					$test = self::WEEKDAY_THURSDAY;
					break;
				case 5:
					$test = self::WEEKDAY_FRIDAY;
					break;
				case 6:
					$test = self::WEEKDAY_SATURDAY;
					break;
				}

				// 2nd Monday, 1st Tuesday etc...
				$currentInstance = ceil((int)$tsTmp->format('j') / 7);
				// Last thursday etc...
				$f_lastwkdayinmonth = $this->dateIsLastWkDayInMonth($tsTmp);

				if ($this->dayOfWeekMask & $test && ($currentInstance==$this->instance || ($this->instance==5 && $f_lastwkdayinmonth)))
				{
					$ret = $tsTmp;
					break;
				}
				else
				{
					$tsTmp->add(new \DateInterval('P1D'));
				}
			}

			// If nothing was found at the end of the month then skip $this->interval weeks
			if (!$ret)
			{
				// Increment year and set to the beginning of the month
				$tsCur->add(new \DateInterval('P' . $this->interval . 'Y'));
				$tsCur->setDate((int)$tsCur->format("Y"), $this->monthOfYear, 1);
			}

			// Increment safty check to guard against infinite loops
			$loops++;

		} while(!$ret && $loops<$maxloops);

		// Check if we have gone beyond the bounds of this pattern (past dateEnd)
		if ($this->dateEnd)
		{
			if ($ret > $this->dateEnd)
			{
				$this->dateProcessedTo = clone $ret;
				$ret = false;
			}
		}

		if ($ret)
		{
			$this->dateProcessedTo = clone $ret;
			return $ret;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Make sure this is a valid pattern given the type
	 *
	 * return bool true if valid, false if there is a problem with $this->lastError set
	 */
	public function validatePattern()
	{
		if (!$this->dateStart) // Required by all
			return false;

		switch ($this->recurType)
		{
		case RECUR_DAILY:
			if ($this->interval)
				return true;
			else
				$this->lastError = "interval is a required param for daily recurrence";
			break;
		case RECUR_WEEKLY:
			if ($this->dayOfWeekMask && $this->interval)
				return true;
			else
				$this->lastError = "Weekly recurrence requires a dayOfWeekMask to be set with interval";
			break;
		case RECUR_MONTHLY:
			if ($this->dayOfMonth && $this->interval)
				return true;
			else
				$this->lastError = "Monthly recurrence requires dayOfMonth and interval params to be set";
			break;
		case RECUR_MONTHNTH:
			if ($this->dayOfWeekMask && $this->instance && $this->interval)
				return true;
			else
				$this->lastError = "Monthnth requires dayOfWeekMask, instance and interval";
			break;
		case RECUR_YEARLY:
			if ($this->monthOfYear && !$this->dayOfMonth && $this->interval)
				return true;
			else
				$this->lastError = "Yearly requires monthOfYear, dayofMonth, and interval params";
			break;
		case RECUR_YEARNTH:
			if ($this->monthOfYear && $this->instance && $this->interval)
				return true;
			else
				$this->lastError = "Yearlynth requires monthOfYear, Instance, and interval params";
			break;
		default:
			// Recurrence type is not set
			return false;
		}
	}

	/**
	 * Determine if a given date is the last weekday of the month.
	 *
	 * @param \DateTime $date The date to check
	 * @return bool true if $date is the last of its weekdays (last sun, mon...) in the month
	 */
	private function dateIsLastWkDayInMonth(\DateTime $date)
	{
		$date_cur = $date->getTimestamp();
		$month = (int)$date->format("m");
		$year = (int)$date->format("Y");

		if ($month == 12)
			$date_next = mktime(0,0,0,1,1,$year+1);
		else
			$date_next = mktime(0,0,0,$month+1,1,$year);

		$last = strtotime("last ".date('l', $date_cur), $date_next);

		return ($date_cur == $last) ? true : false;
	}
}
