<?php

/**
 * Test entity/object class
 */

namespace NetricTest\EntitySync\Collection;

use Netric\EntitySync;
use Netric\EntitySync\Collection;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperInterface;
use PHPUnit\Framework\TestCase;
use Netric\EntityGroupings\DataMapper\EntityGroupingDataMapperFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * @group integration
 */
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
     * Groups to clean up inside the groupings table
     *
     * @var groupId[]
     */
    private $testObjectGroupings = [];

    /**
     * Setup datamapper
     */
    protected function setUp(): void
    {
        // Make sure we don't override parent tearDown
        parent::setUp();

        $this->groupingDataMapper = $this->account->getServiceManager()->get(EntityGroupingDataMapperFactory::class);
    }

    /**
     * Cleanup
     */
    protected function tearDown(): void
    {
        // Make sure we don't override parent tearDown
        parent::tearDown();

        $dm = $this->groupingDataMapper;
        $groupings = $dm->getGroupings(ObjectTypes::CONTACT . "/groups", $this->account->getAccountId());

        // Cleanup the test groupings in groupings table
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
        $collection->setObjType(ObjectTypes::CONTACT);
        $collection->setFieldName("groups");
        return $collection;
    }

    protected function createLocal()
    {
        // Create the grouping below
        $this->groupings = $this->groupingDataMapper->getGroupings(ObjectTypes::CONTACT . "/groups", $this->account->getAccountId());
        $newGroup = $this->groupings->create();
        $newGroup->name = "UTEST CS::testGetExportChanged" . rand();
        $this->groupings->add($newGroup);
        $this->groupingDataMapper->saveGroupings($this->groupings);
        $group = $this->groupings->getByName($newGroup->name);

        $this->testObjectGroupings[] = $newGroup->getGroupId();
        return ["id" => $group->getGroupId(), "revision" => $group->getCommitId()];
    }

    protected function changeLocal($id)
    {
        $group = $this->groupings->getByGuidOrGroupId($id);
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
