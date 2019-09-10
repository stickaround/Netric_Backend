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
use Netric\EntityGroupings\LoaderFactory;
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
        $def = $this->defLoader->get(ObjectTypes::PROJECT_STORY);
        $loader = $this->account->getServiceManager()->get(LoaderFactory::class);
        
        // Get all groupings for this object type
        $groupings = $loader->get(ObjectTypes::PROJECT_STORY, "status_id");

        $completedGroup = $groupings->getByName("Completed");
        $compledtedId = $completedGroup->getValue("id");

        // Test Not Equal
        $condition = new Where("status_id");
        $condition->doesNotEqual($compledtedId);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(status_id!=$compledtedId  or status_id is null)");

        // Test Equals
        $condition = new Where("status_id");
        $condition->equals($compledtedId);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "status_id=$compledtedId");
    }

    public function testbuildConditionStringForFkeyMulti()
    {
        $def = $this->defLoader->get(ObjectTypes::NOTE);
        $objTable = $def->getTable();
        
        // Test Equals
        $condition = new Where("groups");
        $condition->equals(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, " EXISTS (select 1 from  object_grouping_mem where object_grouping_mem.object_id=objects_note.id and (grouping_id=1)) ");
        
        // Test Not Equal
        $condition = new Where("groups");
        $condition->doesNotEqual(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "$objTable.id not in (select object_id from object_grouping_mem where grouping_id=1)");
    }

    public function testbuildConditionStringForObject()
    {
        $def = $this->defLoader->get(ObjectTypes::PROJECT_STORY);
        
        // Test Equals
        $condition = new Where("project_id");
        $condition->equals(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "project_id=1");
    }

    public function testbuildConditionStringForString()
    {
        $def = $this->defLoader->get(ObjectTypes::PROJECT_STORY);
        // Test Equals
        $condition = new Where("name");
        $condition->equals("Project Name");
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "lower(name)='project name'");

        // Test Not Equal
        $condition = new Where("name");
        $condition->doesNotEqual("Project Name");
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "lower(name)!='project name'");

        // Test Contains
        $condition = new Where("name");
        $condition->contains("Project Name");
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "lower(name) like '%project name%'");

        // Test Begins With
        $condition = new Where("name");
        $condition->beginsWith("Project Name");
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "lower(name) like 'project name%'");

        // Greater Than
        $condition = new Where("name");
        $condition->isGreaterThan(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);

        // Should return an empty string since we cannot query greater than with an string field type
        $this->assertEquals($conditionString, "");
    }

    public function testbuildConditionStringForNumber()
    {
        $def = $this->defLoader->get(ObjectTypes::PROJECT_STORY);
        // Test Equals
        $condition = new Where("revision");
        $condition->equals(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "revision=1");

        // Test Not Equal
        $condition = new Where("revision");
        $condition->doesNotEqual(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "(revision!=1 or revision is null)");

        // Greater Than
        $condition = new Where("revision");
        $condition->isGreaterThan(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "revision>1");

        // Greater Than Or Equal To
        $condition = new Where("revision");
        $condition->isGreaterOrEqualTo(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "revision>=1");

        // Less Than
        $condition = new Where("revision");
        $condition->isLessThan(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "revision<1");

        // Less Than Or Equal To
        $condition = new Where("revision");
        $condition->isLessOrEqualTo(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "revision<=1");

        // Contains
        $condition = new Where("revision");
        $condition->contains(1);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);

        // Should return empty string, since we cannot query contains with a number field type
        $this->assertEquals($conditionString, "");
    }
    public function testbuildConditionStringForBoolean()
    {
        $def = $this->defLoader->get(ObjectTypes::PROJECT_STORY);

        // Test Equals
        $condition = new Where("f_seen");
        $condition->equals(true);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "f_seen='true'");

        // Test Not Equal
        $condition = new Where("f_seen");
        $condition->doesNotEqual(true);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "f_seen!='true'");
    }
    public function testbuildConditionStringForDate()
    {
        $def = $this->defLoader->get(ObjectTypes::PROJECT_STORY);

        // Test current day
        $currentDay = date("j");
        $condition = new Where("date_completed");
        $condition->dayIsEqual($currentDay);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "extract(day from date_completed)='$currentDay'");

        // Test current month
        $currentMonth = date("n");
        $condition = new Where("date_completed");
        $condition->monthIsEqual($currentMonth);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "extract(month from date_completed)='$currentMonth'");

        // Test current year
        $currentYear = date("Y");
        $condition = new Where("date_completed");
        $condition->yearIsEqual($currentYear);
        $conditionString = $this->index->buildConditionStringAndSetParams($def, $condition);
        $this->assertEquals($conditionString, "extract(year from date_completed)='$currentYear'");
    }
}
