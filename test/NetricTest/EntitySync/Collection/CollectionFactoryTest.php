<?php

namespace NetricTest\EntitySync\CollectionFactory;

use Netric;

use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntitySync\EntitySync;
use Netric\EntitySync\Collection\CollectionFactory;

/**
 * @group integration
 */
class CollectionFactoryTest extends TestCase
{
    public function testCreateService()
    {
        $account = Bootstrap::getAccount();
        $collectionFactory = new CollectionFactory($account->getServiceManager());

        $data = [
          'object_type' => ObjectTypes::CONTACT,
          'field_name' => 'name',
          'revision' => 1
        ];

        $entityCollection = $collectionFactory->create($account->getAccountId(), EntitySync::COLL_TYPE_ENTITY, $data);

        $this->assertEquals($entityCollection->getAccountId(), $account->getAccountId());
        $this->assertEquals($entityCollection->getRevision(), 1);
        $this->assertEquals($entityCollection->getObjType(), ObjectTypes::CONTACT);
        $this->assertEquals($entityCollection->getFieldName(), 'name');

        // Should be the same with Grouping Collection
        $groupingCollection = $collectionFactory->create($account->getAccountId(), EntitySync::COLL_TYPE_GROUPING, $data);

        $this->assertEquals($groupingCollection->getAccountId(), $account->getAccountId());
        $this->assertEquals($groupingCollection->getRevision(), 1);
        $this->assertEquals($groupingCollection->getObjType(), ObjectTypes::CONTACT);
        $this->assertEquals($groupingCollection->getFieldName(), 'name');
    }
}
