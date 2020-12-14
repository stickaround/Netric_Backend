<?php

/**
 * Test the browser view controller
 */

namespace NetricTest\Controller;

use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Account\Account;
use Netric\Account\AccountContainerInterface;
use Netric\Application\Response\HttpResponse;
use Netric\Authentication\AuthenticationService;
use Netric\Authentication\AuthenticationIdentity;
use Netric\Controller\BrowserViewController;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;
use Netric\Entity\BrowserView\BrowserViewService;
use Netric\Entity\BrowserView\BrowserView;
use Netric\EntityDefinition\ObjectTypes;
use Ramsey\Uuid\Uuid;
use \RuntimeException;

/**
 * @group integration
 */
class BrowserViewControllerTest extends TestCase
{
    /**
     * Initialized controller with mock dependencies
     */
    private BrowserViewController $browserViewController;

    /**
     * Dependency mocks
     */    
    private Account $mockAccount;
    private AuthenticationService $mockAuthService;
    private BrowserViewService $mockBrowserViewService;

    protected function setUp(): void
    {
        // Create mocks        
        $this->mockBrowserViewService = $this->createMock(BrowserViewService::class);

        // Provide identity for mock auth service
        $this->mockAuthService = $this->createMock(AuthenticationService::class);
        $ident = new AuthenticationIdentity('blahaccount', 'blahuser');
        $this->mockAuthService->method('getIdentity')->willReturn($ident);

        // Return mock authenticated account
        $this->mockAccount = $this->createStub(Account::class);
        $this->mockAccount->method('getAccountId')->willReturn(Uuid::uuid4()->toString());

        $accountContainer = $this->createMock(AccountContainerInterface::class);
        $accountContainer->method('loadById')->willReturn($this->mockAccount);

        // Create the controller with mocks
        $this->browserViewController = new BrowserViewController(
            $accountContainer,
            $this->mockAuthService,            
            $this->mockBrowserViewService
        );
        $this->browserViewController->testMode = true;
    }

    /**
     * Test the saving of browser view
     */
    public function testSaveAction()
    {
        $viewId = Uuid::uuid4()->toString();
        $data = [
            'obj_type' => ObjectTypes::CONTACT,
            'id' => $viewId,
            'name' => "unit_test_view",
            'description' => "Unit Test Browser View",
        ];

        // Create browser view for testing
        $mockBrowserView = $this->createMock(BrowserView::class);
        $mockBrowserView->method('isSystem')->willReturn(false);
        $mockBrowserView->method('isDefault')->willReturn(true);

        // Mock the browser view server which is used to save a browser view
        $this->mockBrowserViewService->method('saveView')->willReturn($viewId);
        $this->mockBrowserViewService->method('setDefaultViewForUser')->willReturn(true);
        
        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode($data));
        $response = $this->browserViewController->postSaveAction($request);

        // It should only return the view id saved
        $this->assertEquals($viewId, $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in saving a browser view
     */
    public function testSaveActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->browserViewController->postSaveAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->browserViewController->postSaveAction($request);

        // It should return an error if no object_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());
    }

    /**
     * Test the setting of default browser view
     */
    public function testSetDefaultViewAction()
    {
        $viewId = Uuid::uuid4()->toString();
        $data = [
            'obj_type' => ObjectTypes::CONTACT,
            'id' => $viewId,
            'name' => "unit_test_view",
            'description' => "Unit Test Browser View",
        ];

        // Create browser view for testing
        $mockBrowserView = $this->createMock(BrowserView::class);

        // Mock the browser view server which is used to set a default browser view        
        $this->mockBrowserViewService->method('setDefaultViewForUser')->willReturn(true);
        
        // Make sure postSaveAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode($data));
        $response = $this->browserViewController->postSetDefaultViewAction($request);

        // It should only return the id of the default view
        $this->assertEquals($viewId, $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in setting default view
     */
    public function testSetDefaultViewActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->browserViewController->postSetDefaultViewAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());

        // Make sure postSetDefaultViewAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->browserViewController->postSetDefaultViewAction($request);

        // It should return an error if no object_type is provided in the params
        $this->assertEquals(['error' => 'obj_type is a required param.'], $response->getOutputBuffer());

        // Make sure postSetDefaultViewAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['obj_type' => ObjectTypes::CONTACT, 'id' => null]));
        $response = $this->browserViewController->postSetDefaultViewAction($request);

        // It should return an error if no wer are trying to set a view that is not yet saved
        $this->assertEquals(['error' => 'Browser View should be saved first before setting as the default view.'], $response->getOutputBuffer());
    }

    /**
     * Test the deleting of browser view
     */
    public function testDeleteViewAction()
    {
        $viewId = Uuid::uuid4()->toString();        

        // Create browser view for testing
        $mockBrowserView = $this->createMock(BrowserView::class);

        // Mock the browser view server which is used to delete a browser view        
        $this->mockBrowserViewService->method('deleteView')->willReturn(true);
        
        // Make sure postDeleteViewAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['id' => $viewId]));
        $response = $this->browserViewController->postDeleteViewAction($request);

        // It should only return true when the view is deleted successfully
        $this->assertEquals(true, $response->getOutputBuffer());
    }

    /**
     * Catch the possible errors being thrown when there is a problem in deleting a browser view
     */
    public function testDeleteViewActionCatchingErrors()
    {
        // It should return an error when request input is not valid
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $response = $this->browserViewController->postDeleteViewAction($request);
        $this->assertEquals('Request input is not valid', $response->getOutputBuffer());
        
        // Make sure postDeleteViewAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['bogus' => 'data']));
        $response = $this->browserViewController->postDeleteViewAction($request);

        // It should only return an error when there is no view id provided in the params
        $this->assertEquals(['error' => 'id is a required param.'], $response->getOutputBuffer());

        // Create browser view for testing
        $mockBrowserView = $this->createMock(BrowserView::class);

        // Return false when deleting a view so we can try to catch the error
        $this->mockBrowserViewService->method('deleteView')->willReturn(false);

        // Make sure postDeleteViewAction is called and we get a response
        $request = new HttpRequest();
        $request->setParam('buffer_output', 1);
        $request->setBody(json_encode(['id' => Uuid::uuid4()->toString()]));
        $response = $this->browserViewController->postDeleteViewAction($request);

        // It should only return an error when the deleteView method retruns false
        $this->assertEquals(['error' => 'Error while trying to delete the browser view.'], $response->getOutputBuffer());
    }

    /*
    public function testDeleteAction()
    {
        $data = [
            'obj_type' => ObjectTypes::CONTACT,
            'name' => "unit_test_view_delete",
            'description' => "Unit Test Browser View Delete",
        ];

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $browserViewId = $this->controller->postSaveAction();

        $browserViewService = $this->serviceManager->get(BrowserViewServiceFactory::class);
        $browserView = $browserViewService->getViewById(ObjectTypes::CONTACT, $browserViewId, $this->account->getAccountId());
        $this->testBrowserViews[] = $browserView;

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode(["id" => $browserViewId]));
        $ret = $this->controller->postDeleteViewAction();

        $this->assertTrue($ret, var_export($browserView->toArray(), true));
    }

    public function testSetDefaultViewAction()
    {
        $data = [
            'obj_type' => ObjectTypes::CONTACT,
            'name' => "unit_test_view_default",
            'description' => "Unit Test Browser View Default",
        ];

        // Set params in the request
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $browserViewId = $this->controller->postSaveAction();

        $browserViewService = $this->serviceManager->get(BrowserViewServiceFactory::class);
        $browserView = $browserViewService->getViewById(ObjectTypes::CONTACT, $browserViewId, $this->account->getAccountId());
        $this->testBrowserViews[] = $browserView;

        // Set default view
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($browserView->toArray()));
        $defaultViewId = $this->controller->postSetDefaultViewAction();

        $defaultViewIdForUser = $browserViewService->getDefaultViewForUser(ObjectTypes::CONTACT, $this->user);
        $this->assertEquals($browserViewId, $defaultViewId, var_export($browserView->toArray(), true));
        $this->assertEquals($defaultViewId, $defaultViewIdForUser, var_export($browserView->toArray(), true));
    }

    public function testPostSaveActionToReturnError()
    {
        $data = [
            'id' => 'my_contact',
            'name' => "unit_test_view_default",
            'description' => "Unit Test Browser View Default",
        ];

        // Set params in the request without obj_type key field
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->controller->postSaveAction();

        // It should return an error
        $this->assertEquals($ret['error'], 'obj_type is a required param');

        // Set params in the request with obj_type key field
        $data['obj_type'] = ObjectTypes::CONTACT;
        $req = $this->controller->getRequest();
        $req->setBody(json_encode($data));
        $ret = $this->controller->postSaveAction();

        // It should return an error
        $this->assertEquals(0, strpos($ret['error'], 'Error saving browser view: SQLSTATE[22P02]'));
    }
    */
}
