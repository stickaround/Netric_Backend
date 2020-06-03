<?php
/**
 * Make sure the bin/scripts/update/once/004/001/039.php script works
 */
namespace BinTest\Update\Once;

use Netric\Console\BinScript;
use PHPUnit\Framework\TestCase;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;
use Netric\Db\Relational\RelationalDbFactory;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\EntityDefinitionLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

class Update004001039Test extends TestCase
{
    /**
     * Handle to account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Path to the script to test
     *
     * @var string
     */
    private $scriptPath = null;

    /**
     * Workflows to cleanup
     *
     * @var array
     */
    private $testViews = array();

    /**
     * Setup each test
     */
    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->scriptPath = __DIR__ . "/../../../../bin/scripts/update/once/004/001/039.php";
    }

    protected function tearDown(): void
    {
        $serviceManager = $this->account->getServiceManager();
        $browserViewService = $serviceManager->get(BrowserViewServiceFactory::class);

        foreach ($this->testViews as $view) {
          $browserViewService->deleteView($view);
        }
    }

    /**
     * Make sure the file exists
     *
     * This is more a test of the test to make sure we set the path right, but why
     * not just use unit tests for our tests? :)
     */
    public function testExists()
    {
        $this->assertTrue(file_exists($this->scriptPath), $this->scriptPath . " not found!");
    }

    /**
     * At a basic level, make sure we can run without throwing any exceptions
     */
    public function testRun()
    {
        $user = $this->account->getUser(UserEntity::USER_SYSTEM);

        $serviceManager = $this->account->getServiceManager();
        $browserViewService = $serviceManager->get(BrowserViewServiceFactory::class);
        $entityDefinitionLoader = $serviceManager->get(EntityDefinitionLoaderFactory::class);
        $db = $serviceManager->get(RelationalDbFactory::class);
        $def = $entityDefinitionLoader->get(ObjectTypes::TASK);

        // Mock the old object views that are using user_id instead of owner_id
        $viewId = $db->insert("app_object_views", ["user_id" => $user->getId(), "object_type_id" => $def->getId()]);
        
        // Execute the script
        $binScript = new BinScript($this->account->getApplication(), $this->account);
        $this->assertTrue($binScript->run($this->scriptPath));

        // After running the script, the object view should now have owner_id
        $browserViewService->clearViewsCache();
        $view = $browserViewService->getViewById(ObjectTypes::TASK, $viewId);
        $this->testViews[] = $view;

        $this->assertEquals($view->getOwnerId(), $user->getGuid());
    }
}