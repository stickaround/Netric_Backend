<?php
/**
 * Test querying ElasticSearch server
 *
 * Most tests are inherited from IndexTestsAbstract.php.
 * Only define index specific tests here and try to avoid name collision with the tests
 * in the parent class. For the most part, the parent class tests all public functions
 * so private functions should be tested below.
 */
namespace NetricTest\EntityQuery\Index;

use Netric;
use PHPUnit\Framework\TestCase;
use Netric\EntityQuery\Index\EntityQueryIndexRdb;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Where;

class EntityQueryIndexRdbTest extends IndexTestsAbstract
{
    /**
     * Use this funciton in all the indexes to construct the datamapper
     *
     * @return EntityDefinition_DataMapperInterface
     */
    protected function getIndex()
    {
        return new EntityQueryIndexRdb($this->account);
    }
    
    /**
     * Dummy test
     */
    public function testDummy()
    {
        $this->assertTrue(true);
    }

    public function testbuildConditionStringForFkey()
    {
        $def = $this->defLoader->get(ObjectTypes::TASK);
        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        
        // Get all groupings for this object type
        $groupings = $groupingLoader->get(ObjectTypes::TASK . "/status_id");

        $completedGroup = $groupings->getByName("Completed");
        $compledtedId = $completedGroup->getValue("id");

        // Test Not Equal
        $condition = new Where("status_id");
        $condition->doesNotEqual($compledtedId);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(field_data->>'status_id' != '$compledtedId' OR field_data->>'status_id' IS NULL)");

        // Test Equals
        $condition = new Where("status_id");
        $condition->equals($compledtedId);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "field_data->>'status_id' = '$compledtedId'");
    }

    public function testbuildConditionStringForFkeyMulti()
    {
        $def = $this->defLoader->get(ObjectTypes::NOTE);
        $objTable = $def->getTable();
        
        // Test Equals
        $condition = new Where("groups");
        $condition->equals(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, " EXISTS (select 1 from  object_grouping_mem where object_grouping_mem.object_id = nullif(objects_note.field_data->>'id', '')::int and (object_grouping_mem.grouping_id = 1)) ");
        
        // Test Not Equal
        $condition = new Where("groups");
        $condition->doesNotEqual(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "nullif(field_data->>'id', '')::int NOT IN (select object_id from object_grouping_mem where object_grouping_mem.grouping_id = 1)");
    }

    public function testbuildConditionStringForObject()
    {
        $def = $this->defLoader->get(ObjectTypes::TASK);
        
        // Test Equals
        $condition = new Where("user_id");
        $condition->equals(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals("field_data->>'user_id' = '1'", $conditionString);
    }

    public function testbuildConditionStringForString()
    {
        $def = $this->defLoader->get(ObjectTypes::TASK);
        // Test Equals
        $condition = new Where("name");
        $condition->equals("Task Name");
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "lower(field_data->>'name') = 'task name'");

        // Test Not Equal
        $condition = new Where("name");
        $condition->doesNotEqual("Task Name");
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "lower(field_data->>'name') != 'task name'");

        // Test Contains
        $condition = new Where("name");
        $condition->contains("Task Name");
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "lower(field_data->>'name') LIKE '%task name%'");

        // Test Begins With
        $condition = new Where("name");
        $condition->beginsWith("Task Name");
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "lower(field_data->>'name') LIKE 'task name%'");

        // Greater Than
        $condition = new Where("name");
        $condition->isGreaterThan(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);

        // Should return an empty string since we cannot query greater than with an string field type
        $this->assertEquals($conditionString, "");
    }

    public function testbuildConditionStringForNumber()
    {
        $def = $this->defLoader->get(ObjectTypes::TASK);
        // Test Equals
        $condition = new Where("revision");
        $condition->equals(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'revision', ''))::integer = '1'");

        // Test Equals for timestamp field type
        $condition = new Where("ts_entered");
        $condition->equals(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'ts_entered', ''))::timestamp with time zone = '" . date("Y-m-d") . "'");

        // Test Equals for date field type
        $condition = new Where("start_date");
        $condition->equals(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'start_date', ''))::date = '" . date("Y-m-d") . "'");

        // Test Not Equals
        $condition = new Where("revision");
        $condition->doesNotEqual(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "((nullif(field_data->>'revision', ''))::integer != '1' OR field_data->>'revision' IS NULL)");

        // Test Not Equals for timestamp field type
        $condition = new Where("ts_entered");
        $condition->doesNotEqual(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "((nullif(field_data->>'ts_entered', ''))::timestamp with time zone != '" . date("Y-m-d") . "' OR field_data->>'ts_entered' IS NULL)");

        // Test Not Equals for date field type
        $condition = new Where("start_date");
        $condition->doesNotEqual(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "((nullif(field_data->>'start_date', ''))::date != '" . date("Y-m-d") . "' OR field_data->>'date_start' IS NULL)");

        // Greater Than
        $condition = new Where("revision");
        $condition->isGreaterThan(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'revision', ''))::integer > '1'");

        // Test Greater Than for timestamp field type
        $condition = new Where("ts_entered");
        $condition->isGreaterThan(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'ts_entered', ''))::timestamp with time zone > '" . date("Y-m-d") . "'");

        // Test Greater Than for date field type
        $condition = new Where("start_date");
        $condition->isGreaterThan(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'start_date', ''))::date > '" . date("Y-m-d") . "'");

        // Greater Than Or Equal To
        $condition = new Where("revision");
        $condition->isGreaterOrEqualTo(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'revision', ''))::integer >= '1'");

        // Test Greater Than or Equal To for timestamp field type
        $condition = new Where("ts_entered");
        $condition->isGreaterOrEqualTo(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'ts_entered', ''))::timestamp with time zone >= '" . date("Y-m-d") . "'");

        // Test Greater Than or Equal TO for date field type
        $condition = new Where("start_date");
        $condition->isGreaterOrEqualTo(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'start_date', ''))::date >= '" . date("Y-m-d") . "'");

        // Less Than
        $condition = new Where("revision");
        $condition->isLessThan(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'revision', ''))::integer < '1'");

        // Test Less Than for timestamp field type
        $condition = new Where("ts_entered");
        $condition->isLessThan(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'ts_entered', ''))::timestamp with time zone < '" . date("Y-m-d") . "'");

        // Test Less Than for date field type
        $condition = new Where("start_date");
        $condition->isLessThan(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'start_date', ''))::date < '" . date("Y-m-d") . "'");

        // Less Than Or Equal To
        $condition = new Where("revision");
        $condition->isLessOrEqualTo(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'revision', ''))::integer <= '1'");

        // Test Less Than or Equal To for timestamp field type
        $condition = new Where("ts_entered");
        $condition->isLessOrEqualTo(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'ts_entered', ''))::timestamp with time zone <= '" . date("Y-m-d") . "'");

        // Test Less Than or Equal To for date field type
        $condition = new Where("start_date");
        $condition->isLessOrEqualTo(date("Y-m-d"));
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'start_date', ''))::date <= '" . date("Y-m-d") . "'");

        // Contains
        $condition = new Where("revision");
        $condition->contains(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);

        // Should return empty string, since we cannot query contains with a number field type
        $this->assertEquals($conditionString, "");
    }

    public function testbuildConditionStringForBoolean()
    {
        $def = $this->defLoader->get(ObjectTypes::TASK);

        // Test Equals
        $condition = new Where("f_seen");
        $condition->equals(true);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'f_seen', ''))::boolean = true");

        // Test Not Equal
        $condition = new Where("f_seen");
        $condition->doesNotEqual(true);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(nullif(field_data->>'f_seen', ''))::boolean != true");
    }
    
    public function testbuildConditionStringForDate()
    {
        $def = $this->defLoader->get(ObjectTypes::TASK);

        // Test current day
        $currentDay = date("j");
        $condition = new Where("date_completed");
        $condition->dayIsEqual($currentDay);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "extract(day from (nullif(field_data->>'date_completed', ''))::date) = '$currentDay'");

        // Test current month
        $currentMonth = date("n");
        $condition = new Where("date_completed");
        $condition->monthIsEqual($currentMonth);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "extract(month from (nullif(field_data->>'date_completed', ''))::date) = '$currentMonth'");

        // Test current year
        $currentYear = date("Y");
        $condition = new Where("date_completed");
        $condition->yearIsEqual($currentYear);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "extract(year from (nullif(field_data->>'date_completed', ''))::date) = '$currentYear'");
    }
}
