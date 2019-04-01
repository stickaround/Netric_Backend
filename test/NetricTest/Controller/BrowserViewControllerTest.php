<?php

/**
 * Test the browser view controller
 */
namespace NetricTest\Controller;

use Netric\Controller\BrowserViewController;
use Netric\Entity\BrowserView\BrowserView;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\EntityDefinition\ObjectTypes;

/**
 * @group integration
 */
class BrowserViewControllerTest extends TestCase
{
    /**
     * Account used for testing
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Controller instance used for testing
     *
     * @var EntityController
     */
    protected $controller = null;

    /**
     * Test browser views that should be cleaned up on tearDown
     *
     * @var BrowserViewInterface[]
     */
    private $testBrowserViews = [];

    protected function setUp(): void
{
        $this->account = Bootstrap::getAccount();
        $this->user = $this->account->getUser();

        // Get the service manager of the current user
        $this->serviceManager = $this->account->getServiceManager();

        // Create the controller
        $this->controller = new BrowserViewController($this->account->getApplication(), $this->account);
        $this->controller->testMode = true;
    }

    /**
     * Cleanup after a test runs
     */
    protected function tearDown(): void
{
        // Cleanup test browser views
        $browserViewService = $this->serviceManager->get(BrowserViewServiceFactory::class);
        foreach ($this->testBrowserViews as $browserView) {
            $browserViewService->deleteView($browserView);
        }
    }

    public function testSaveAction()
    {
        $data = array(
            'obj_type' => ObjectTypes::CONTACT,
            'name' => "unit_test_view",
            'description' => "Unit Test Browser View",
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $browserViewId = $this->controller->postSaveAction();

        $browserViewService = $this->serviceManager->get(BrowserViewServiceFactory::class);
        $browserView = $browserViewService->getViewById(ObjectTypes::CONTACT, $browserViewId);
        $browserViewData = $browserView->toArray();
        $this->testBrowserViews[] = $browserView;

        $this->assertGreaterThan(0, $browserViewId);
        $this->assertEquals($data['name'], $browserView->getName(), var_export($browserViewData, true));
        $this->assertEquals($data['description'], $browserViewData['description'], var_export($browserViewData, true));
    }

    public function testDeleteAction()
    {
        $data = array(
            'obj_type' => ObjectTypes::CONTACT,
            'name' => "unit_test_view_delete",
            'description' => "Unit Test Browser View Delete",
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $browserViewId = $this->controller->postSaveAction();

        $browserViewService = $this->serviceManager->get(BrowserViewServiceFactory::class);
        $browserView = $browserViewService->getViewById(ObjectTypes::CONTACT, $browserViewId);
        $this->testBrowserViews[] = $browserView;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode(array ("id" => $browserViewId)));
        $ret = $this->controller->postDeleteViewAction();

        $this->assertTrue($ret, var_export($browserView->toArray(), true));
    }

    public function testSetDefaultViewAction()
    {
        $data = array(
            'obj_type' => ObjectTypes::CONTACT,
            'name' => "unit_test_view_default",
            'description' => "Unit Test Browser View Default",
        );

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $browserViewId = $this->controller->postSaveAction();

        $browserViewService = $this->serviceManager->get(BrowserViewServiceFactory::class);
        $browserView = $browserViewService->getViewById(ObjectTypes::CONTACT, $browserViewId);
        $this->testBrowserViews[] = $browserView;

        // Set default view
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($browserView->toArray()));
        $defaultViewId = $this->controller->postSetDefaultViewAction();

        $defaultViewIdForUser = $browserViewService->getDefaultViewForUser(ObjectTypes::CONTACT, $this->user);
        $this->assertEquals($browserViewId, $defaultViewId, var_export($browserView->toArray(), true));
        $this->assertEquals($defaultViewId, $defaultViewIdForUser, var_export($browserView->toArray(), true));
    }
}
