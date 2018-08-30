<?php
/**
 * Test entity activity class
 */
namespace NetricTest\Entity\ObjType;

use Netric\Entity;
use PHPUnit\Framework\TestCase;
use Netric\Entity\ObjType\FolderEntity;
use Netric\FileSystem\FileSystemFactory;

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
        $this->user = $this->account->getUser(\Netric\Entity\ObjType\UserEntity::USER_SYSTEM);
    }

    private function createTestFile()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $loader = $account->getServiceManager()->get("EntityLoader");
        $dataMapper = $this->getEntityDataMapper();

        $file = $loader->create("file");
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
        $entity = $this->account->getServiceManager()->get("EntityFactory")->create("folder");
        $this->assertInstanceOf(FolderEntity::class, $entity);
    }

    public function testGetRootFolder()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        $loader = $account->getServiceManager()->get("EntityLoader");
        $entity = $this->account->getServiceManager()->get(EntityFactory::class)->create("folder");
        $rootFolderEntity = $entity->getRootFolder();
    }
}
