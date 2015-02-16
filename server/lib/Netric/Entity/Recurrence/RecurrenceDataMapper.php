<?php
/**
 * Database DataMapper for recurrence pattern
 *
 * This DataMapper is typically loaded from the service manager
 * with $serviceLocation->get("Entity_RecurrenceDataMapper")
 * which will setup all the necessary dependencies.
 * 
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */
namespace Netric\Entity\Recurrence;

use Netric\Db;

class RecurrenceDataMapperDb extends \Netric\DataMapperAbstract
{
	/**
	 * Database handle
	 *
	 * @var \Netric\Db\DbInterface
	 */
	private function $dbh = null;

	/**
	 * Class constructor to set up dependencies
	 *
	 * @param \Netric\Account
	 * @param \Netric\Db\DbInterface
	 */
	public function __construct(\Netric\Account $account, DbInterface $dbh)
	{
		// The base DataMapper always has a reference to account
		parent::__construct($account);

		$this->dbh = $dbh;
	}
	
	/**
	 * TODO: The below code used to be inside CRecurrencePattern but
	 * we have moved it into a datamapper to separate persistence logic 
	 * from business logic.
	 *
	 * We need to use public get and set functions in the recurrence pattern
	 * to save all the properties for the recurrence pattern.
	 *
	 * @param \Netric\Entity\Recurrence\RecurrencePattern $recurPattern
	 * @return int Unique id of the pattern on success or null on failure this $this->lastError set
	 */
	public function save(RecurrencePattern $recurPattern)
	{
		if (!$this->validatePattern())
		{
			return false;
		}

		$dbh = $this->dbh;
		$toUpdate = array();
		$toupdate['object_type_id'] = $dbh->escapeNumber($this->object_type_id);
		$toupdate['object_type'] = "'".$dbh->escape($this->object_type)."'";
		$toupdate['date_processed_to'] = $dbh->escapeDate($this->dateProcessedTo);
		$toupdate['parent_object_id'] = $dbh->escapeNumber($this->parentId);

		$toupdate['type'] = $dbh->escapeNumber($this->recurType);
		$toupdate['interval'] = $dbh->escapeNumber($this->interval);
		$toupdate['date_start'] = $dbh->escapeDate($this->dateStart);
		$toupdate['date_end'] = $dbh->escapeDate($this->dateEnd);
		$toupdate['t_start'] = $dbh->escapeDate($this->timeStart);
		$toupdate['t_end'] = $dbh->escapeDate($this->timeEnd);
		$toupdate['dayofmonth'] = $dbh->escapeNumber($this->dayOfMonth);
		//$toupdate['duration'] = $dbh->escapeNumber($this->duration);
		$toupdate['instance'] = $dbh->escapeNumber($this->instance);
		$toupdate['monthofyear'] = $dbh->escapeNumber($this->monthOfYear);
		$toupdate['f_active'] = ($this->fActive) ? "'t'" : "'f'";
		$toupdate['ep_locked'] = $this->epLocked;

		if ($this->id && $dbh->getNumRows($dbh->query("select id from object_recurrence WHERE id=".$dbh->escapeNumber($this->id))))
		{
			$upd = "";
			foreach ($toupdate as $fname=>$fval)
			{
				if ($upd) $upd .= ", ";
				$upd .= $fname."=".$fval;
			}

			if ($upd) $upd .= ", ";
			$upd .= "dayofweekmask[1]='".(($this->dayOfWeekMask & WEEKDAY_SUNDAY) ? 't' : 'f')."', ";
			$upd .= "dayofweekmask[2]='".(($this->dayOfWeekMask & WEEKDAY_MONDAY) ? 't' : 'f')."', ";
			$upd .= "dayofweekmask[3]='".(($this->dayOfWeekMask & WEEKDAY_TUESDAY) ? 't' : 'f')."', ";
			$upd .= "dayofweekmask[4]='".(($this->dayOfWeekMask & WEEKDAY_WEDNESDAY) ? 't' : 'f')."', ";
			$upd .= "dayofweekmask[5]='".(($this->dayOfWeekMask & WEEKDAY_THURSDAY) ? 't' : 'f')."', ";
			$upd .= "dayofweekmask[6]='".(($this->dayOfWeekMask & WEEKDAY_FRIDAY) ? 't' : 'f')."', ";
			$upd .= "dayofweekmask[7]='".(($this->dayOfWeekMask & WEEKDAY_SATURDAY) ? 't' : 'f')."'";

			$query = "UPDATE object_recurrence SET $upd where id='".$this->id."'; select '".$this->id."' as id;";
		}
		else
		{
			if ($this->useId)
				$toupdate['id'] = $this->useId;

			$flds = "";
			$vls = "";
			foreach ($toupdate as $fname=>$fval)
			{
				if ($flds) 
				{	
					$flds .= ", ";
					$vls .= ", ";
				}

				$flds .= $fname;
				$vls .= $fval;
			}

			if ($flds) 
			{	
				$flds .= ", ";
				$vls .= ", ";
			}

			$flds	.= "dayofweekmask[1],";
			$vls	.="'".(($this->dayOfWeekMask & WEEKDAY_SUNDAY) ? 't' : 'f')."', ";
			$flds	.= "dayofweekmask[2],";
			$vls	.="'".(($this->dayOfWeekMask & WEEKDAY_MONDAY) ? 't' : 'f')."', ";
			$flds	.= "dayofweekmask[3],";
			$vls	.="'".(($this->dayOfWeekMask & WEEKDAY_TUESDAY) ? 't' : 'f')."', ";
			$flds	.= "dayofweekmask[4],";
			$vls	.="'".(($this->dayOfWeekMask & WEEKDAY_WEDNESDAY) ? 't' : 'f')."', ";
			$flds	.= "dayofweekmask[5],";
			$vls	.="'".(($this->dayOfWeekMask & WEEKDAY_THURSDAY) ? 't' : 'f')."', ";
			$flds	.= "dayofweekmask[6],";
			$vls	.="'".(($this->dayOfWeekMask & WEEKDAY_FRIDAY) ? 't' : 'f')."', ";
			$flds	.= "dayofweekmask[7]";
			$vls	.="'".(($this->dayOfWeekMask & WEEKDAY_SATURDAY) ? 't' : 'f')."'";

			$query = "INSERT INTO object_recurrence($flds) VALUES($vls); ";
			
			if ($this->useId)
				$query .= "select '".$this->useId."' as id;";
			else
				$query .= "select currval('object_recurrence_id_seq') as id;";
		}

		//echo $query;
		$result = $dbh->query($query);
		if (!$this->id)
		{
			if ($dbh->getNumRows($result))
			{
				$this->id = $dbh->getValue($result, 0, "id");
			}
		}

		if ($this->debug)
			echo "<pre>SAVE: ".var_export($toupdate,true)."</pre>";

		return $this->id;
	}

	/**
	 * Secure a unique id to use before it is saved
	 *
	 * TODO: create a unit test
	 *
	 * @return int
	 */
	public function getNextId()
	{
		$dbh = $this->dbh;
		$ret = false;

		$query = "select nextval('object_recurrence_id_seq') as id;";
		$result = $dbh->query($query);
		if ($dbh->getNumRows($result))
		{
			$ret = $dbh->getValue($result, 0, "id");
			$this->useId = $ret;
		}

		return $ret;
	}

	/**
	 * Load up an entity recurrence pattern by id
	 *
	 * TODO: like the above function(s), this used to be part of the recurrence pattern
	 * object. We are moving it into a separate datamapper.
	 *
	 * @param id $id The unique id of the pattern to load
	 *
	 * @return \Netric\Entity\Recurrence\RecurrencePattern
	 */
	public function load($id)
	{
		$dbh = $this->dbh;
		$query = "select object_type_id, object_type, date_processed_to, parent_object_id, type, interval, date_start, 
					date_end, dayofmonth, instance, monthofyear, ep_locked,
					dayofweekmask[1] as day1, dayofweekmask[2] as day2, dayofweekmask[3] as day3, dayofweekmask[4] as day4,
					dayofweekmask[5] as day5, dayofweekmask[6] as day6, dayofweekmask[7] as day7
					from object_recurrence where id='".$id."'";
		//echo "<pre>$query</pre>";
		$result = $dbh->query($query);
		if ($dbh->getNumRows($result))
		{
			$row = $dbh->GetRow($result, 0);

			foreach ($row as $name=>$val)
				$this->arrChangeLog[$name] = $val;

			$this->object_type_id = $row['object_type_id'];
			$this->object_type = $row['object_type'];
			$this->dateProcessedTo = $row['date_processed_to'];
			$this->parentId = $row['parent_object_id'];
			$this->recurType = $row['type'];
			$this->interval = $row['interval'];
			$this->dateStart = $row['date_start'];
			$this->dateEnd = $row['date_end'];
            
            if(isset($row['calendar_id']))
			    $this->calendarId = $row['calendar_id'];
                
            if(isset($row['t_start']))
			    $this->timeStart = $row['t_start'];
                
            if(isset($row['t_end']))
			    $this->timeEnd = $row['t_end'];
            
            if(isset($row['dayofmonth']))
			    $this->dayOfMonth = $row['dayofmonth'];
                
            if(isset($row['duration']))
			    $this->duration = $row['duration'];
                
            if(isset($row['instance']))
			    $this->instance = $row['instance'];
                
            if(isset($row['monthofyear']))
			    $this->monthOfYear = $row['monthofyear'];

            if(isset($row['ep_locked']))
			    $this->epLocked = $row['ep_locked'];

            $this->fAllDay = (isset($row['all_day']) && $row['all_day']=='t') ? true : false;
            
			if ($row['day1'] == 't')
				$this->dayOfWeekMask = $this->dayOfWeekMask | WEEKDAY_SUNDAY;
			if ($row['day2'] == 't')
				$this->dayOfWeekMask = $this->dayOfWeekMask | WEEKDAY_MONDAY;
			if ($row['day3'] == 't')
				$this->dayOfWeekMask = $this->dayOfWeekMask | WEEKDAY_TUESDAY;
			if ($row['day4'] == 't')
				$this->dayOfWeekMask = $this->dayOfWeekMask | WEEKDAY_WEDNESDAY;
			if ($row['day5'] == 't')
				$this->dayOfWeekMask = $this->dayOfWeekMask | WEEKDAY_THURSDAY;
			if ($row['day6'] == 't')
				$this->dayOfWeekMask = $this->dayOfWeekMask | WEEKDAY_FRIDAY;
			if ($row['day7'] == 't')
				$this->dayOfWeekMask = $this->dayOfWeekMask | WEEKDAY_SATURDAY;

			$dbh->FreeResults($result);

			// Load recurrence rules
			if ($row['object_type'])
			{
				$odef = new CAntObject($dbh, $row['object_type']);
				$this->fieldDateStart = $odef->def->recurRules['field_date_start'];
				$this->fieldTimeStart = $odef->def->recurRules['field_time_start'];
				$this->fieldDateEnd = $odef->def->recurRules['field_date_end'];
				$this->fieldTimeEnd = $odef->def->recurRules['field_time_end'];
			}

			return true;
		}

		return false;
	}

	/**
	 * Delete recurrence pattern by id
	 *
	 * TODO: write unit test for this
	 *
	 * @param int $id The unique id of the recurring pattern to delete
	 */
	public function removeById($id)
	{
		if (!is_numeric($id))
			return false;

		if ($this->dbh->query("delete from object_recurrence where id='" . $id . "'"))
		{
			return true;
		}
		else
		{
			$this->lastError = $this->dbh->getLastError();
			return false;
		}
	}
