<?php
namespace NetricTest\Controller;

use Netric\Entity\EntityLoader;
use Netric\Account\Account;
use Netric\Controller\EmailController;
use Netric\Entity\ObjType\UserEntity;
use Netric\FileSystem\FileSystem;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery;

/**
 * Test calling the email controller
 */
class EmailControllerTest extends TestCase
{
    /**
     * Account used for testing
     *
     * @var Account
     */
    protected $account = null;

    /**
     * Controller instance used for testing
     *
     * @var FilesController
     */
    protected $controller = null;

    /**
     * Test user
     *
     * @var UserEntity
     */
    private $user = null;


    /**
     * Common constants used
     *
     * @cons string
     */
    const TEST_USER = "test_email_controller";
    const TEST_USER_PASS = "testpass";

    protected function setUp()
    {
        $this->account = Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
        $loader = $sl->get(EntityLoader::class);

        // Create the controller
        $this->controller = new EmailController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;

        // Get FileSystem
        $this->fileSystem = $sl->get(FileSystem::class);

        // Make sure old test user does not exist
        $query = new EntityQuery(ObjectTypes::USER);
        $query->where('name')->equals(self::TEST_USER);
        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $res = $index->executeQuery($query);
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $user = $res->getEntity($i);
            $loader->delete($user, true);
        }

        // Create a temporary user
        $user = $loader->create(ObjectTypes::USER);
        $user->setValue("name", self::TEST_USER);
        $user->setValue("password", self::TEST_USER_PASS);
        $user->setValue("active", true);
        $loader->save($user);
        $this->user = $user;
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown()
    {
        // Remote the temp user
        $this->account = Bootstrap::getAccount();
        $sl = $this->account->getServiceManager();
        $entityLoader = $sl->get(EntityLoader::class);
        $entityLoader->delete($this->user, true);
    }

    /**
     * Try sending a draft email
     */
    public function testPostSendAction()
    {
//        $req = $this->controller->getRequest();
//        $req->setParam("files", $testUploadedFiles);
//        $req->setParam("path", "/testUpload");
//        $ret = $this->controller->postUploadAction();

        $this->assertTrue(true);
    }
}
