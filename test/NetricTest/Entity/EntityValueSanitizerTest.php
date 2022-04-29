<?php

/**
 * Test entity value sanitizer class that is responsible for sanitizing entity field values
 */

namespace NetricTest\Entity;

use Netric\Account\Account;
use Netric\Entity\EntityLoader;
use PHPUnit\Framework\TestCase;
use Netric\Entity\EntityFactoryFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\EntityValueSanitizer;
use Netric\Entity\EntityValueSanitizerFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityQuery\EntityQuery;
use NetricTest\Bootstrap;

class EntityValueSanitizerTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var Account
     */
    private $account = null;

    /**
     * Handle to the entity loader for creating and loading entities
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Sanitizer for entity values
     *
     * @var EntityValueSanitizer
     */
    private $entityValueSanitizer = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->entityLoader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $this->entityValueSanitizer = $this->account->getServiceManager()->get(EntityValueSanitizerFactory::class);
    }

    /**
     * Entity Sanitizer should be able to sanitize entity query conditions
     */
    public function testSanitizeEntityQuery()
    {
        $dateStr = strtotime("January 01, 2020");
        $currentUser = $this->account->getAuthenticatedUser();

        $query = new EntityQuery(
            ObjectTypes::PROJECT,
            $this->account->getAccountId(),
            $currentUser->getEntityId()
        );
        $query->where('name')->equals("Test Query");
        $query->where('date_deadline')->equals(date("Y-m-d", $dateStr));
        $query->where('members')->equals(UserEntity::USER_CURRENT);

        $ret = $this->entityValueSanitizer->sanitizeQuery($query);

        // There should be no changes in value since this is just a regular text
        $this->assertEquals($ret[0]->value, "Test Query");

        $this->assertEquals($ret[1]->value, "2020-01-01");
        $this->assertEquals($ret[2]->value, $currentUser->getEntityId());
    }

    /**
     * Entity Sanitizer should be able to sanitize entity query grouping conditions
     */
    public function testSanitizeEntityQueryGroupingField()
    {
        $query = new EntityQuery(ObjectTypes::USER, $this->account->getAccountId());
        $query->where('groups')->equals(UserEntity::GROUP_USERS);
        $ret = $this->entityValueSanitizer->sanitizeQuery($query);

        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $userGroups = $groupingLoader->get(ObjectTypes::USER . '/groups', $this->account->getAccountId());
        $usersGroup = $userGroups->getByName(UserEntity::GROUP_USERS);

        $this->assertEquals($ret[0]->value, $usersGroup->getGroupId());
    }

    /**
     * Entity Sanitizer should be able to sanitize entity query boolean conditions
     */
    public function testSanitizeEntityQueryBooleanConditions()
    {
        $query = new EntityQuery(ObjectTypes::TASK, $this->account->getAccountId());
        $query->where('is_closed')->equals(true);
        $ret = $this->entityValueSanitizer->sanitizeQuery($query);
        $this->assertEquals($ret[0]->value, "true");

        $query = new EntityQuery(ObjectTypes::TASK, $this->account->getAccountId());
        $query->where('is_closed')->equals(false);
        $ret = $this->entityValueSanitizer->sanitizeQuery($query);
        $this->assertEquals($ret[0]->value, "false");
    }

    /**
     * Entity Sanitizer should be able sanitize entity field boolean
     */
    public function testSanitizeFieldBoolean()
    {
        // Create a test task
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue('is_closed', 'f');

        // Test the sanitizing of date fields
        $ret = $this->entityValueSanitizer->sanitizeEntity($task);

        $this->assertEquals($ret['is_closed'], false);

        // Now lets try setting the boolean value to "t"
        $task->setValue('is_closed', "t");
        $ret = $this->entityValueSanitizer->sanitizeEntity($task);
        $this->assertEquals($ret['is_closed'], true);
    }

    /**
     * Entity Sanitizer should be able sanitize entity field date
     */
    public function testSanitizeFieldDate()
    {
        $dateStr = strtotime("January 01, 2020");
        $dateWithTimeStr = strtotime("10:30pm January 01, 2020");

        // Create a test task
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue("deadline", date("Y-m-d", $dateStr));
        $task->setValue("date_entered", date("Y-m-d h:i:sa", $dateWithTimeStr));

        // Test the sanitizing of date fields
        $ret = $this->entityValueSanitizer->sanitizeEntity($task);

        $this->assertEquals($ret["deadline"], $dateStr);
        $this->assertEquals($ret["date_entered"], $dateWithTimeStr);
    }

    /**
     * Entity Sanitizer should be able sanitize entity field object values
     */
    public function testSanitizeFieldObjectValues()
    {
        $currentUser = $this->account->getAuthenticatedUser();

        // Create a test task
        $task = $this->entityLoader->create(ObjectTypes::TASK, $this->account->getAccountId());
        $task->setValue("owner_id", UserEntity::USER_CURRENT, 'current.user');

        // Test the sanitizing of object values
        $ret = $this->entityValueSanitizer->sanitizeEntity($task);

        $this->assertEquals($ret["owner_id"], $currentUser->getEntityId());
        $this->assertEquals($ret["owner_id_fval"], $currentUser->getName());
    }

    /**
     * Entity Sanitizer should be able sanitize entity field object_multi values
     */
    public function testSanitizeFieldObjectMultiValues()
    {
        $currentUser = $this->account->getAuthenticatedUser();

        // Create a test project
        $project = $this->entityLoader->create(ObjectTypes::PROJECT, $this->account->getAccountId());
        $project->addMultiValue("members", UserEntity::USER_CURRENT, 'current.user');
        $project->addMultiValue("members", 21231313131, 'test member1');
        $project->addMultiValue("members", 555555, 'test member5');

        // Test the sanitizing of object multi values
        $ret = $this->entityValueSanitizer->sanitizeEntity($project);

        $this->assertContains($currentUser->getEntityId(), $ret["members"], "testSanitizeFieldObjectMultiValues did not sanitize the current user");
        $this->assertEquals($ret["members_fval"][$currentUser->getEntityId()], $currentUser->getName());
    }

    /**
     * Entity Sanitizer should be able sanitize entity field grouping_multi values
     */
    public function testSanitizeFieldGroupingMultiValues()
    {
        // Create a test user
        $user = $this->entityLoader->create(ObjectTypes::USER, $this->account->getAccountId());
        $user->addMultiValue("groups", UserEntity::GROUP_USERS, 'group.users');
        $user->addMultiValue("groups", UserEntity::GROUP_EVERYONE, 'group.everyone');
        $user->addMultiValue("groups", UserEntity::GROUP_CREATOROWNER, 'group.creatorowner');
        $user->addMultiValue("groups", UserEntity::GROUP_ADMINISTRATORS, 'group.administrators');

        // Make sure default groups are set correctly
        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $userGroups = $groupingLoader->get(ObjectTypes::USER . '/groups', $this->account->getAccountId());

        $usersGroup = $userGroups->getByName(UserEntity::GROUP_USERS);
        $everyoneGroup = $userGroups->getByName(UserEntity::GROUP_EVERYONE);
        $creatorOwnerGroup = $userGroups->getByName(UserEntity::GROUP_CREATOROWNER);
        $administratorsGroup = $userGroups->getByName(UserEntity::GROUP_ADMINISTRATORS);

        // Test the sanitizing of grouping_multi values
        $ret = $this->entityValueSanitizer->sanitizeEntity($user);

        // Test that the groups were sanitized
        $this->assertContains($usersGroup->getGroupId(), $ret["groups"]);
        $this->assertContains($everyoneGroup->getGroupId(), $ret["groups"]);
        $this->assertContains($creatorOwnerGroup->getGroupId(), $ret["groups"]);
        $this->assertContains($administratorsGroup->getGroupId(), $ret["groups"]);

        // Test the value names
        $this->assertEquals($ret["groups_fval"][$usersGroup->getGroupId()], $usersGroup->getName());
        $this->assertEquals($ret["groups_fval"][$everyoneGroup->getGroupId()], $everyoneGroup->getName());
        $this->assertEquals($ret["groups_fval"][$creatorOwnerGroup->getGroupId()], $creatorOwnerGroup->getName());
        $this->assertEquals($ret["groups_fval"][$administratorsGroup->getGroupId()], $administratorsGroup->getName());
    }
}
