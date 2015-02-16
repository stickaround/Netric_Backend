<?php
/**
 * Test recurrence for entities
 */
namespace NetricTest\Entity\Recurrence;

use Netric\Entity\Recurrence\RecurrencePattern;
use PHPUnit_Framework_TestCase;

class RecurrencePatternTest extends PHPUnit_Framework_TestCase 
{
    /**
     * Tennant account
     * 
     * @var \Netric\Account
     */
    private $account = null;
    
    /**
     * Administrative user
     * 
     * @var \Netric\User
     */
    private $user = null;
    

	/**
	 * Setup each test
	 */
	protected function setUp() 
	{
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(\Netric\User::USER_ADMINISTRATOR);
	}

	public function testConstructor()
	{
		$pattern = new RecurrencePattern();
		$this->assertInstanceof('\Netric\Entity\Recurrence\RecurrencePattern', $pattern);
	}

	/**
	 * This appears to be more of a datamapper test
	 */
	/*
	function testRecurWeeklyBitwise() 
	{
		$rp = new CRecurrencePattern($this->dbh);
		$rp->type = RECUR_WEEKLY;
		$rp->interval = 1;
		$rp->dateStart = "1/2/2011"; // First sunday
		$rp->dateEnd = "1/15/2011";
		$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_MONDAY;
		$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_WEDNESDAY;
		// Test before save
		$this->assertNotNull($rp->dayOfWeekMask & WEEKDAY_MONDAY);
		$this->assertNotNull($rp->dayOfWeekMask & WEEKDAY_WEDNESDAY);

		// Save and unset for reloading
		$rid = $rp->save();
		unset($rp);

		// Open and test
		$rp = new CRecurrencePattern($this->dbh, $rid);
		$this->assertEquals($rp->type, RECUR_WEEKLY);
		$this->assertNotNull($rp->dayOfWeekMask & WEEKDAY_MONDAY);
		$this->assertNotNull($rp->dayOfWeekMask & WEEKDAY_WEDNESDAY);

		// Cleanup
		$rp->remove();
	}
	*/

	public function testSetDayOfWeek()
	{
		$rp = new RecurrencePattern();
		$rp->setDayOfWeek(RecurrencePattern::WEEKDAY_SUNDAY, true);
		$this->assertNotEquals(0, $rp->getDayOfWeekMask() & RecurrencePattern::WEEKDAY_SUNDAY);
		$this->assertEquals(0, $rp->getDayOfWeekMask() & RecurrencePattern::WEEKDAY_MONDAY);

		// Flip days and test again
		$rp->setDayOfWeek(RecurrencePattern::WEEKDAY_MONDAY, true);
		$rp->setDayOfWeek(RecurrencePattern::WEEKDAY_SUNDAY, false);
		$this->assertNotEquals(0, $rp->getDayOfWeekMask() & RecurrencePattern::WEEKDAY_MONDAY);
		$this->assertEquals(0, $rp->getDayOfWeekMask() & RecurrencePattern::WEEKDAY_SUNDAY);
	}

	public function testGetNextStart_Daily()
	{
		$rp = new RecurrencePattern();
		$rp->setRecurType(RecurrencePattern::RECUR_DAILY);
		$rp->setInterval(1);
		$rp->setDateStart(new \DateTime("1/1/2010"));
		$rp->setDateEnd( new \DateTime("3/1/2010"));
		
		// First instance should be today
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/01/2010"));

		// Next instance should be tomorrow
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/02/2010"));
		
		// Change interval to skip a day and rewind to set
		$rp->setInterval(2);
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/04/2010"));

		// Call again should skip another day
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/06/2010"));
	}

	public function testGetNextStart_Weekly()
	{
		$rp = new RecurrencePattern();
		$rp->setRecurType(RecurrencePattern::RECUR_WEEKLY);
		$rp->setInterval(1);
		$rp->setDateStart(new \DateTime("1/2/2011")); // First sunday
		$rp->setDateEnd( new \DateTime("1/15/2011"));
		$rp->setDayOfWeek(RecurrencePattern::WEEKDAY_SUNDAY, true);
		$rp->setDayOfWeek(RecurrencePattern::WEEKDAY_WEDNESDAY, true);

		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/02/2011")); // Sun
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/05/2011")); // Wed
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/09/2011")); // Sun
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/12/2011")); // Wed

		// Next should fail because it is beyond the endDate
		$tsNext = $rp->getNextStart();
		$this->assertFalse($tsNext);
	}

	public function testGetNextStart_Monthly()
	{
		$rp = new RecurrencePattern();
		$rp->setRecurType(RecurrencePattern::RECUR_MONTHLY);
		$rp->setInterval(1);
		$rp->setDayOfMonth(1);
		$rp->setDateStart(new \DateTime("1/1/2011")); // First sunday

		// Should be the first of each month
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/01/2011"));
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("02/01/2011"));
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("03/01/2011"));

		// Skip over non-existant dates
		$rp = new RecurrencePattern();
		$rp->setRecurType(RecurrencePattern::RECUR_MONTHLY);
		$rp->setInterval(1);
		$rp->setDayOfMonth(30);
		$rp->setDateStart(new \DateTime("1/1/2011")); // First sunday

		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/30/2011"));
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("03/30/2011")); // Should skip of ver 2/30 because does not exist
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("04/30/2011"));

	}

	public function testGetNextStart_MonthNth()
	{
		$rp = new RecurrencePattern();
		$rp->setRecurType(RecurrencePattern::RECUR_MONTHNTH);
		$rp->setDayOfWeek(RecurrencePattern::WEEKDAY_SUNDAY, true);
		$rp->setInterval(1);
		$rp->setInstance(RecurrencePattern::NTH_4TH); // The 4th Sunday of each month
		$rp->setDayOfMonth(1);
		$rp->setDateStart(new \DateTime("1/1/2011")); // First sunday

		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/23/2011")); // The 4th Sunday in January
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("02/27/2011")); // The 4th Sunday in February

		// Test last
		$rp = new RecurrencePattern();
		$rp->setRecurType(RecurrencePattern::RECUR_MONTHNTH);
		$rp->setDayOfWeek(RecurrencePattern::WEEKDAY_SUNDAY, true);
		$rp->setInterval(1);
		$rp->setInstance(RecurrencePattern::NTH_LAST); // The last sunday
		$rp->setDayOfMonth(1);
		$rp->setDateStart(new \DateTime("1/1/2011")); // First sunday

		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/30/2011")); // The last Sunday in January
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("02/27/2011")); // The last Sunday in February
	}

	public function testGetNextStart_Yearly()
	{
		$rp = new RecurrencePattern();
		$rp->setRecurType(RecurrencePattern::RECUR_YEARLY);
		$rp->setInterval(1);
		$rp->setDayOfMonth(8);
		$rp->setMonthOfYear(10);
		$rp->setDateStart(new \DateTime("1/1/2011")); // First sunday
		$rp->setDateEnd(new \DateTime("1/1/2013")); // First sunday

		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("10/08/2011"));
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("10/08/2012"));
		$tsNext = $rp->getNextStart();
		$this->assertFalse($tsNext); // Past the dateEnd
	}
	
	public function testGetNextStart_YearNth()
	{
		$rp = new RecurrencePattern();
		$rp->setRecurType(RecurrencePattern::RECUR_YEARNTH);
		// The 4th Sunday of January
		$rp->setInstance(RecurrencePattern::NTH_4TH);
		$rp->setDayOfWeek(RecurrencePattern::WEEKDAY_SUNDAY, true);
		$rp->setMonthOfYear(1);
		$rp->setInterval(1);

		$rp->setDateStart(new \DateTime("1/1/2011"));
		$rp->setDateEnd(new \DateTime("1/1/2013"));

		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/23/2011"));
		$tsNext = $rp->getNextStart();
		$this->assertEquals($tsNext, new \DateTime("01/22/2012"));
		$tsNext = $rp->getNextStart();
		$this->assertFalse($tsNext); // Past the dateEnd
	}

	/**************************************************************************
	 * Function: 	testRecurInternalFunctions
	 *
	 * Purpose:		Test internal functions like save, ischanged, etc...
	 **************************************************************************/
	/*
	function testRecurInternalFunctions() 
	{
		$dbh = $this->dbh;

		// Create calendar event for testing
		$obj = new CAntObject($dbh, "calendar_event", null, $this->user);
		$obj->setValue("name", "testRecurInternalFunctions");
		$obj->setValue("ts_start", "1/2/2011 12:00 PM PST");
		$obj->setValue("ts_end", "1/2/2011 12:30 PM PST");
		$eid = $obj->save();

		// Test save & open
		$rp = new CRecurrencePattern($dbh);
		$rp->type = RECUR_WEEKLY;
		$rp->interval = 1;
		$rp->dateStart = "1/1/2011";
		$rp->dateEnd = "1/30/2011";
		$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_SUNDAY;
		$rp->object_type_id = $obj->object_type_id;
		$rp->object_type = $obj->object_type;
		$rp->parentId = $eid;
		$rp->fieldDateStart = "ts_start";
		$rp->fieldTimeStart = "ts_start";
		$rp->fieldDateEnd = "ts_end";
		$rp->fieldTimeEnd = "ts_end";
		$rpid = $rp->save();
		unset($rp);

		// Test open
		$rp = new CRecurrencePattern($dbh, $rpid);
		$this->assertEquals($rp->type, RECUR_WEEKLY);
		$this->assertEquals($rp->interval, 1);
		$this->assertEquals(strtotime($rp->dateStart), strtotime("1/1/2011"));
		$this->assertEquals(strtotime($rp->dateEnd), strtotime("1/30/2011"));

		// Test isChanged with above opened rp
		$this->assertFalse($rp->isChanged());
		$rp->interval = 2;
		$this->assertTrue($rp->isChanged());
		unset($rp);
		
		// Test isChanged with weekdaymask
		$rp = new CRecurrencePattern($dbh, $rpid);
		$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_WEDNESDAY; // Add wednesday
		$this->assertTrue($rp->isChanged());

		// Test delete
		$this->assertTrue($rp->remove());

		$obj->remove();
		$obj->remove();
	}
	*/

	/**
	 * @group recur
	 */
	/*
	function testRecur() 
	{
		$dbh = $this->dbh;
		
		$obj = new CAntObject($dbh, "task", null, $this->user);
		$obj->setValue("name", "My Recurring Task");
		$obj->setValue("start_date", "1/1/2011");
		$obj->setValue("deadline", "1/1/2011");
		$rp = $obj->getRecurrencePattern();
		$rp->type = RECUR_DAILY;
		$rp->interval = 1;
		$rp->dateStart = "1/1/2011";
		$rp->dateEnd = "3/1/2011";
		$obj->save();

		$this->assertNotNull($rp->id);

		$created = $rp->createInstances("1/3/2011");
		$this->assertEquals($created, 2); // should create two additional objects

		$obj->remove();

		// Make sure recurrence pattern is flagged inactive
		$this->assertEquals($dbh->GetNumberRows($dbh->Query("select * from object_recurrence where id='".$rp->id."' and f_active is false")), 1);

		$obj->remove();

		// Make sure recurrence pattern is purged
		$this->assertEquals($dbh->GetNumberRows($dbh->Query("select * from object_recurrence where id='".$rp->id."'")), 0);


		// Now test using CAntObjectList::checkForRecurrence
		$obj = new CAntObject($dbh, "task", null, $this->user);
		$obj->setValue("name", "My Recurring Task");
		$obj->setValue("start_date", "1/1/2011");
		$obj->setValue("deadline", "1/1/2011");
		$rp = $obj->getRecurrencePattern();
		$rp->type = RECUR_DAILY;
		$rp->interval = 1;
		$rp->dateStart = "1/1/2011";
		$rp->dateEnd = "1/3/2011";
		$oid = $obj->save();

		$this->assertNotNull($rp->id);

		$objList = new CAntObjectList($dbh, "task", $this->user);
		$objList->addCondition("and", "start_date", "is_less", "5/1/2011"); // Should fire create instances
		$objList->getObjects();

		// Make sure there are three recurring tasks created
		$this->assertEquals(3, $dbh->GetNumberRows($dbh->Query("select id from project_tasks where recurrence_pattern='".$rp->id."'")));

		// Cleanup
		$rp->removeSeries();
		$obj->removeHard();

		// Test with an event due to using timestamps rather than dates
		$obj = new CAntObject($dbh, "calendar_event", null, $this->user);
		$obj->setValue("name", "My Recurring Event");
		$obj->setValue("ts_start", "1/5/2011 09:00 AM");
		$obj->setValue("ts_end", "1/5/2011 10:00 AM");
		$rp = $obj->getRecurrencePattern();
		$rp->type = RECUR_WEEKLY;
		$rp->interval = 1;
		$rp->dateStart = "1/5/2011"; // First Wednesday
		$rp->dateEnd = "1/19/2011"; // Third Wednesday
		$rp->dayOfWeekMask = $rp->dayOfWeekMask | WEEKDAY_WEDNESDAY;
		$obj->save();

		$this->assertNotNull($rp->id);

		$objList = new CAntObjectList($dbh, "calendar_event", $this->user);
		$objList->addCondition("and", "ts_start", "is_greater_or_equal", "1/1/2011"); // Should fire create instances
		$objList->addCondition("and", "ts_start", "is_less_or_equal", "5/1/2011"); // Should fire create instances
		$objList->getObjects();

		// Make sure there are three recurring tasks created
		$this->assertEquals(3, $dbh->GetNumberRows($dbh->Query("select id from calendar_events where recurrence_pattern='".$rp->id."'")));

		// Cleanup
		$rp->removeSeries();
		$obj->removeHard();
	}
	*/
}
