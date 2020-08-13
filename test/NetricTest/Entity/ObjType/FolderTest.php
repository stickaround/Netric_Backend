<?php

/**
 * Test entity activity class
 */

namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexFactory;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\FolderEntity;
use Netric\FileSystem\FileSystemFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use NetricTest\Bootstrap;

class FolderTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
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
    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    private function createTestFile()
    {
        $account = Bootstrap::getAccount();
        $loader = $account->getServiceManager()->get(EntityLoaderFactory::class);
        $dataMapper = $this->getEntityDataMapper();

        $file = $loader->create(ObjectTypes::FILE, $this->account->getAccountId());
        $file->setValue("name", "test.txt");
        $dataMapper->save($file, $this->user);

        $this->testFiles[] = $file;

        return $file;
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $this->assertInstanceOf(FolderEntity::class, $entity);
    }

    public function testGetRootFolder()
    {
        $account = Bootstrap::getAccount();
        $loader = $account->getServiceManager()->get(EntityLoaderFactory::class);
        $entity = $loader->create(ObjectTypes::FOLDER, $this->account->getAccountId());
        $rootFolderEntity = $entity->getRootFolder();

        $this->assertNotNull($rootFolderEntity);

        // Make sure that there is only 1 root folder
        $query = new EntityQuery(ObjectTypes::FOLDER, $this->account->getAccountId());
        $query->where("parent_id")->equals("");
        $query->andWhere("name")->equals("/");
        $query->andWhere("f_system")->equals(true);

        $entityIndex = $account->getServiceManager()->get(IndexFactory::class);
        $result = $entityIndex->executeQuery($query);
        $this->assertEquals($result->getNum(), 1);
    }
}
