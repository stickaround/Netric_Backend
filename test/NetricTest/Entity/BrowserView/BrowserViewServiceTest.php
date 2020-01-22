<?php
/**
 * Test the browser view service for getting browser views for a user
 */
namespace NetricTest\Entity\BrowserView;

use Netric\Entity\BrowserView\BrowserView;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;
use Netric\EntityQuery;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoaderFactory;

class BrowserViewServiceTest extends TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account\Account
     */
    private $account = null;

    /**
     * Form service
     *
     * @var \Netric\Entity\BrowserView\BrowserViewService
     */
    private $browserViewService = null;

    /**
     * Administrative user
     *
     * We test for this user since he will never have customized forms
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
        $sm = $this->account->getServiceManager();
        $this->browserViewService = $sm->get(BrowserViewServiceFactory::class);
        $this->user = $this->account->getUser(UserEntity::USER_SYSTEM);
    }

    /**
     * Test saving a view to the database
     */
    public function testSaveView()
    {
        $data = array(
            'obj_type' => ObjectTypes::CONTACT,
            'conditions' => array(
                array(
                    'blogic' => 'and',
                    'field_name' => 'name',
                    'operator' => 'is_equal',
                    'value' => 'test',
                ),
            ),
            'table_columns' => array(
                'first_name'
            ),
            'order_by' => array(
                array(
                    "field_name" => "name",
                    "direction" => ""
                )
            )
        );
        $view = new BrowserView();
        $view->fromArray($data);

        $ret = $this->browserViewService->saveView($view);
        $this->assertTrue(is_numeric($ret));

        // Make sure save set the view id
        $this->assertNotNull($view->getId());

        // Cleanup
        $this->browserViewService->deleteView($view);
    }

    /**
     * Make sure we can load a view from the database
     */
    public function testLoadView()
    {
        $data = array(
            'obj_type' => ObjectTypes::CONTACT,
            'conditions' => array(
                array(
                    'blogic' => 'and',
                    'field_name' => 'name',
                    'operator' => 'is_equal',
                    'value' => 'test',
                ),
            ),
            'table_columns' => array(
                'first_name'
            ),
            'order_by' => array(
                array(
                    "field_name" => "name",
                    "direction" => ""
                )
            )
        );
        $view = new BrowserView();
        $view->fromArray($data);
        $vid = $this->browserViewService->saveView($view);

        // Load and test the values
        $loaded = $this->browserViewService->getViewById(ObjectTypes::CONTACT, $vid);
        $this->assertNotNull($loaded);
        $this->assertEquals($loaded->getObjType(), $data['obj_type']);
        $this->assertEquals(count($data['conditions']), count($view->getConditions()));
        $this->assertEquals(count($data['table_columns']), count($view->getTableColumns()));
        $this->assertEquals(count($data['order_by']), count($view->getOrderBy()));
    }

    /**
     * We should be able to delete a view from the database by id
     */
    public function testDeleteView()
    {
        // Save a very simple view
        $view = new BrowserView();
        $view->setObjType(ObjectTypes::NOTE);
        $vid = $this->browserViewService->saveView($view);

        // Delete it
        $ret = $this->browserViewService->deleteView($view);
        $this->assertTrue($ret);

        // Make sure we cannot load it from cache
        $loadView = $this->browserViewService->getViewById($view->getObjType(), $vid);
        $this->assertNull($loadView);

        // Now make sure we cannot load it from the DB
        $this->browserViewService->clearViewsCache();
        $loadView = $this->browserViewService->getViewById($view->getObjType(), $vid);
        $this->assertNull($loadView);
    }

    /**
     * We shold not be able to delete a system view
     */
    public function testGetSystemViews()
    {
        // Use note because we know it has at least one BrowserView defined: default
        $sysViews = $this->browserViewService->getSystemViews(ObjectTypes::PROJECT_STORY);
        $this->assertTrue(count($sysViews) >= 1);
        $this->assertInstanceOf(BrowserView::class, $sysViews[0]);

        // We know the 2nd view in data/browser_views/project_story.php has a condition
        $conditions = $sysViews[1]->getConditions();
        $this->assertEquals("status_id", $conditions[0]->fieldName);

        $sm = $this->account->getServiceManager();
        $groupingLoader = $sm->get(GroupingLoaderFactory::class);
        $groupings = $groupingLoader->get(ObjectTypes::PROJECT_STORY . "/status_id");

        // We should be able to get the groupings by id using the condition value
        $group = $groupings->getById($conditions[0]->value);
        $this->assertNotFalse($group);
    }

    /**
     * Make sure that getting account views will not return team or user views
     */
    public function testGetAccountViews()
    {
        // Setup team vuew
        $teamView = new BrowserView();
        $teamView->setObjType(ObjectTypes::NOTE);
        $teamView->setTeamId(1);
        $this->browserViewService->saveView($teamView);

        // Setup user view
        $userView = new BrowserView();
        $userView->setObjType(ObjectTypes::NOTE);
        $userView->setUserId(1);
        $this->browserViewService->saveView($userView);

        // Set global account view
        $accountView  = new BrowserView();
        $accountView->setObjType(ObjectTypes::NOTE);
        $this->browserViewService->saveView($accountView);

        // Make sure getting accounts views does not return user or team views
        $accountViews = $this->browserViewService->getAccountViews(ObjectTypes::NOTE);
        $foundUserView = false;
        $foundTeamView = false;
        foreach ($accountViews as $view) {
            if ($view->getUserId()) {
                $foundUserView = true;
            }
            if ($view->getTeamId()) {
                $foundTeamView = true;
            }
        }
        $this->assertTrue(count($accountViews) >= 1);
        $this->assertFalse($foundUserView);
        $this->assertFalse($foundTeamView);

        // Cleanup
        $this->browserViewService->deleteView($teamView);
        $this->browserViewService->deleteView($userView);
        $this->browserViewService->deleteView($accountView);
    }

    /**
     * Make sure that getting team views only returns team and not user and account views
     */
    public function testGetTeamViews()
    {
        // Setup team vuew
        $teamView = new BrowserView();
        $teamView->setObjType(ObjectTypes::NOTE);
        $teamView->setTeamId(1);
        $this->browserViewService->saveView($teamView);

        // Setup user view
        $userView = new BrowserView();
        $userView->setObjType(ObjectTypes::NOTE);
        $userView->setUserId(2);
        $this->browserViewService->saveView($userView);

        // Set global account view
        $accountView  = new BrowserView();
        $accountView->setObjType(ObjectTypes::NOTE);
        $this->browserViewService->saveView($accountView);

        // Make sure getting accounts views does not return user or team views
        $teamViews = $this->browserViewService->getTeamViews(ObjectTypes::NOTE, 1);
        $foundUserView = false;
        $foundAccountView = false;
        foreach ($teamViews as $view) {
            if ($view->getUserId()) {
                $foundUserView = true;
            }
            if (empty($view->getTeamId()) && empty($view->getUserId())) {
                $foundAccountView = true;
            }
        }
        $this->assertTrue(count($teamViews) >= 1);
        $this->assertFalse($foundUserView);
        $this->assertFalse($foundAccountView);

        // Cleanup
        $this->browserViewService->deleteView($teamView);
        $this->browserViewService->deleteView($userView);
        $this->browserViewService->deleteView($accountView);
    }

    /**
     * Make sure that getting user views only returns user and not team and account views
     */
    public function testGetUserViews()
    {
        // Setup team view
        $teamView = new BrowserView();
        $teamView->setObjType(ObjectTypes::NOTE);
        $teamView->setTeamId(1);
        $this->browserViewService->saveView($teamView);

        // Setup user view
        $userView = new BrowserView();
        $userView->setObjType(ObjectTypes::NOTE);
        $userView->setUserId(2);
        $this->browserViewService->saveView($userView);

        // Set global account view
        $accountView  = new BrowserView();
        $accountView->setObjType(ObjectTypes::NOTE);
        $this->browserViewService->saveView($accountView);

        // Make sure getting accounts views does not return user or team views
        $userViews = $this->browserViewService->getUserViews(ObjectTypes::NOTE, 2);
        $foundTeamView = false;
        $foundAccountView = false;
        foreach ($userViews as $view) {
            if ($view->getTeamid()) {
                $foundTeamView = true;
            }
            if (empty($view->getTeamId()) && empty($view->getUserId())) {
                $foundAccountView = true;
            }
        }
        $this->assertTrue(count($userViews) >= 1);
        $this->assertFalse($foundTeamView);
        $this->assertFalse($foundAccountView);

        // Cleanup
        $this->browserViewService->deleteView($teamView);
        $this->browserViewService->deleteView($userView);
        $this->browserViewService->deleteView($accountView);
    }

    /**
     * Make sure we get a merged array of views only for a specific user
     */
    public function testGetViewsForUser()
    {
        // Set temp view id for testing if not set
        if (empty($this->user->getValue("team_id"))) {
            $this->user->setValue("team_id", 3);
        }

        // Setup team vuew
        $teamView = new BrowserView();
        $teamView->setObjType(ObjectTypes::NOTE);
        $teamView->setTeamId($this->user->getValue("team_id"));
        $this->browserViewService->saveView($teamView);

        // Setup user view
        $userView = new BrowserView();
        $userView->setObjType(ObjectTypes::NOTE);
        $userView->setUserId($this->user->getId());
        $this->browserViewService->saveView($userView);

        // Set global account view
        $accountView  = new BrowserView();
        $accountView->setObjType(ObjectTypes::NOTE);
        $this->browserViewService->saveView($accountView);

        // Make sure we get at least the number of added views plus the sytem
        $usersViews = $this->browserViewService->getViewsForUser(ObjectTypes::NOTE, $this->user);
        $this->assertTrue(count($usersViews) >= 4);

        // Cleanup
        $this->browserViewService->deleteView($teamView);
        $this->browserViewService->deleteView($userView);
        $this->browserViewService->deleteView($accountView);
    }

    /**
     * Make sure we can get the default view for a user
     */
    public function testGetDefaultViewForUser()
    {
        $defId = $this->browserViewService->getDefaultViewForUser(ObjectTypes::NOTE, $this->user);
        $this->assertNotNull($defId);
    }
}
