<?php
/**
 * Test entity activity class
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\FolderEntity;
use Netric\FileSystem\FileSystemFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;

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
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);
    }

    private function createTestFile()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $loader = $account->getServiceManager()->get(EntityLoaderFactory::class);
        $dataMapper = $this->getEntityDataMapper();

        $file = $loader->create(ObjectTypes::FILE);
        $file->setValue("name", "test.txt");
        $dataMapper->save($file);

        $this->testFiles[] = $file;

        return $file;
    }

    /**
     * Test dynamic factory of entity
     */
    public function testFactory()
    {
        $entity = $this->account->getServiceManager()->get(EntityLoaderFactory::class)->create(ObjectTypes::FOLDER);
        $this->assertInstanceOf(FolderEntity::class, $entity);
    }

    public function testGetRootFolder()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $loader = $account->getServiceManager()->get(EntityLoaderFactory::class);
        $entity = $loader->create(ObjectTypes::FOLDER);
        $rootFolderEntity = $entity->getRootFolder();
    }
}
