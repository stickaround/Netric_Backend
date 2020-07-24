<?php

/**
 * Test entity groupings loader class that is responsible for creating and initializing exisiting objects
 */
namespace NetricTest\EntityGroupings;

use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\EntityGroupings\GroupingLoaderFactory;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;

class EntityGroupingStateManagerTest extends TestCase
{
    /**
     * Tenant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
    }

    /**
     * Test loading an object definition
     */
    public function testGet()
    {
        $dm = $this->account->getServiceManager()->get(EntityGroupingDataMapperFactory::class);

        // Create test group
        $groupings = $dm->getGroupings(ObjectTypes::CONTACT . "/groups");
        $newGroup = $groupings->create();
        $newGroup->name = "uttest-eg-loader-get";
        $groupings->add($newGroup);
        $dm->saveGroupings($groupings);


        // Load through loader
        $groupingLoader = $this->account->getServiceManager()->get(GroupingLoaderFactory::class);
        $groupingLoader->clearCache(ObjectTypes::CONTACT, "groups");

        // Use the loader to get the object
        $grp = $groupingLoader->get(ObjectTypes::CONTACT . "/groups")->getByName($newGroup->name);
        $this->assertNotNull($grp);
        $this->assertEquals($newGroup->name, $grp->name);

        // Test to see if the isLoaded function indicates the entity has been loaded and cached locally
        $refIm = new \ReflectionObject($groupingLoader);
        $isLoaded = $refIm->getMethod("isLoaded");
        $isLoaded->setAccessible(true);
        $this->assertTrue($isLoaded->invoke($groupingLoader, ObjectTypes::CONTACT . "/groups"));

        // TODO: Test to see if it is cached
        /*
        $refIm = new \ReflectionObject($groupingLoader);
        $getCached = $refIm->getMethod("getCached");
        $getCached->setAccessible(true);
        $this->assertTrue(is_array($getCached->invoke($groupingLoader, ObjectTypes::CONTACT, $cid)));
         * *
         */

        // Cleanup
        $groups = $groupingLoader->get(ObjectTypes::CONTACT . "/groups");
        $grp = $groups->getByName($newGroup->name);
        $groups->delete($grp->id);
        $groups->save();
    }
}
