<?php

/**
 * Test entity/object class
 */
namespace NetricTest\EntitySync\Collection;

use Netric\EntitySync;
use Netric\EntitySync\Collection;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use PHPUnit\Framework\TestCase;

class GroupingCollectionTest extends AbstractCollectionTests
{
    /**
     * Entity DataMapper
     *
     * @var EntityGroupingDataMapperInterface
     */
    private $groupingDataMapper = null;

    /**
     * Groupings object
     *
     * @var \Netric\EntityGrouping
     */
    private $groupings = null;

    /**
     * Groups to clean up inside the object_groupings table
     *
     * @var groupId[]
     */
    private $testObjectGroupings = [];

    /**
     * Setup datamapper
     */
    protected function setUp()
    {
        // Make sure we don't override parent tearDown
        parent::setUp();

        $this->groupingDataMapper = $this->account->getServiceManager()->get('Netric\EntityGroupings\DataMapper\EntityGroupingDataMapper');
    }

    /**
     * Cleanup
     */
    protected function tearDown()
    {
        // Make sure we don't override parent tearDown
        parent::tearDown();

        $dm = $this->groupingDataMapper;
        $groupings = $dm->getGroupings("customer", "groups");

        // Cleanup the test groupings in object_groupings table
        foreach ($this->testObjectGroupings as $groupId) {
            $groupings->delete($groupId);
        }

        $dm->saveGroupings($groupings);
    }

    /**
     * Required by AbstractCollectionTests
     */
    protected function getCollection()
    {

        $collection = new Collection\GroupingCollection($this->esDataMapper, $this->commitManager, $this->groupingDataMapper);
        $collection->setObjType("customer");
        $collection->setFieldName("groups");
        return $collection;
    }

    protected function createLocal()
    {
        // Create the grouping below
        $this->groupings = $this->groupingDataMapper->getGroupings("customer", "groups");
        $newGroup = $this->groupings->create();
        $newGroup->name = "UTEST CS::testGetExportChanged" . rand();
        $this->groupings->add($newGroup);
        $this->groupingDataMapper->saveGroupings($this->groupings);
        $group = $this->groupings->getByName($newGroup->name);

        $this->testObjectGroupings[] = $newGroup->id;
        return array("id" => $group->id, "revision" => $group->commitId);
    }

    protected function changeLocal($id)
    {
        $group = $this->groupings->getById($id);
        // Record a change to the grouping
        $group->name = "UTEST CS::testGetExportChanged" . rand();
        $group->setDirty(true);
        $this->groupingDataMapper->saveGroupings($this->groupings);
    }

    protected function deleteLocal($id = null)
    {
        if ($this->groupings) {
            if ($id) {
                $this->groupings->delete($id);
            }

            $this->groupingDataMapper->saveGroupings($this->groupings);
        }
    }
}
