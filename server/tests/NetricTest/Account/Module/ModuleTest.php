<?php
/**
 * Test a module in netric like - messages, work, etc...
 */
namespace NetricTest\Account\Module;

use Netric\Account\Module\Module;
use PHPUnit_Framework_TestCase;

class ModuleTest extends PHPUnit_Framework_TestCase
{
    public function testFromArray()
    {
        $data = array(
           "id" => 123,
            "name" => "test",
            "title" => "My Test Module",
            "short_title" => "Test",
            "scope" => Module::SCOPE_EVERYONE,
            "system" => false,
            "user_id" => 234,
            "team_id" => 345,
            "sort_order" => 100,
        );

        $module = new Module();
        $module->fromArray($data);

        $this->assertEquals($data['id'], $module->getId());
        $this->assertEquals($data['name'], $module->getName());
        $this->assertEquals($data['title'], $module->getTitle());
        $this->assertEquals($data['short_title'], $module->getShortTitle());
        $this->assertEquals($data['scope'], $module->getScope());
        $this->assertEquals($data['system'], $module->isSystem());
        $this->assertEquals($data['user_id'], $module->getUserId());
        $this->assertEquals($data['team_id'], $module->getTeamId());
        $this->assertEquals($data['sort_order'], $module->getSortOrder());
    }

    public function testToArray()
    {
        $module = new Module();
        $module->setId(123);
        $module->setName("tester");
        $module->setTitle("My Tester Module");
        $module->setShortTitle("Tester");
        $module->setScope(Module::SCOPE_TEAM);
        $module->setSystem(false);
        $module->setUserId(234);
        $module->setTeamId(345);
        $module->setSortOrder(3000);

        $data = $module->toArray();
        $this->assertEquals($data['id'], $module->getId());
        $this->assertEquals($data['name'], $module->getName());
        $this->assertEquals($data['title'], $module->getTitle());
        $this->assertEquals($data['short_title'], $module->getShortTitle());
        $this->assertEquals($data['scope'], $module->getScope());
        $this->assertEquals($data['system'], $module->isSystem());
        $this->assertEquals($data['user_id'], $module->getUserId());
        $this->assertEquals($data['team_id'], $module->getTeamId());
        $this->assertEquals($data['sort_order'], $module->getSortOrder());
    }
}
