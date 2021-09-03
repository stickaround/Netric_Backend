<?php

/**
 * Test the browser view service for getting browser views for a user
 */

namespace NetricTest\Entity\BrowserView;

use Netric\Entity\BrowserView\BrowserView;
use Netric\Entity\BrowserView\BrowserViewServiceFactory;
use Netric\EntityQuery\EntityQuery;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityGroupings\GroupingLoaderFactory;
use Ramsey\Uuid\Uuid;

class BrowserViewServiceTest extends TestCase
{
    /**
     * Arbitrary IDs
     */
    const TEST_TEAM_ID = 'a4bf054d-7499-40d9-83a2-98ec023b233f';
    const TEST_USER_ID = 'a4bf054d-7450-40d9-83a2-98ec023b233f';

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
     * Browser views that will be deleted after testing
     */
    private $testViews = [];

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
        $this->user = $this->account->getUser(null, UserEntity::USER_SYSTEM);
    }

    /**
     * Cleanup any test browser views
     */
    protected function tearDown(): void
    {
        foreach ($this->testViews as $view) {
            $this->browserViewService->deleteView($view);
        }
    }

    /**
     * Test saving a view to the database
     */
    public function testSaveView()
    {
        $data = [
            'obj_type' => ObjectTypes::CONTACT,
            'conditions' => [
                [
                    'blogic' => 'and',
                    'field_name' => 'name',
                    'operator' => 'is_equal',
                    'value' => 'test',
                ],
            ],
            'table_columns' => [
                'first_name'
            ],
            'group_first_order_by' => true,
            'order_by' => [
                [
                    "field_name" => "name",
                    "direction" => ""
                ]
            ]
        ];
        $view = new BrowserView();
        $view->fromArray($data);

        $ret = $this->browserViewService->saveView($view, $this->account->getAccountId());
        $this->testViews[] = $view;

        $this->assertTrue(is_numeric($ret));

        // Make sure save set the view id
        $this->assertNotNull($view->getId());

        // Test group_first_order_by
        $viewData = $view->toArray();
        $this->assertEquals($viewData['group_first_order_by'], true);

        // Cleanup
        $this->browserViewService->deleteView($view);
    }

    /**
     * Make sure we can load a view from the database
     */
    public function testLoadView()
    {
        $data = [
            'obj_type' => ObjectTypes::CONTACT,
            'conditions' => [
                [
                    'blogic' => 'and',
                    'field_name' => 'name',
                    'operator' => 'is_equal',
                    'value' => 'test',
                ],
            ],
            'table_columns' => [
                'first_name'
            ],
            'order_by' => [
                [
                    "field_name" => "name",
                    "direction" => ""
                ]
            ]
        ];
        $view = new BrowserView();
        $view->fromArray($data);
        $vid = $this->browserViewService->saveView($view, $this->account->getAccountId());
        $this->testViews[] = $view;

        // Load and test the values
        $loaded = $this->browserViewService->getViewById(ObjectTypes::CONTACT, $vid, $this->account->getAccountId());
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
        $vid = $this->browserViewService->saveView($view, $this->account->getAccountId());
        $this->testViews[] = $view;

        // Delete it
        $ret = $this->browserViewService->deleteView($view);
        $this->assertTrue($ret);

        // Make sure we cannot load it from cache
        $loadView = $this->browserViewService->getViewById($view->getObjType(), $vid, $this->account->getAccountId());
        $this->assertNull($loadView);

        // Now make sure we cannot load it from the DB
        $this->browserViewService->clearViewsCache();
        $loadView = $this->browserViewService->getViewById($view->getObjType(), $vid, $this->account->getAccountId());
        $this->assertNull($loadView);
    }

    /**
     * We shold not be able to delete a system view
     */
    public function testGetSystemViews()
    {
        // Use task because we know it has at least one BrowserView defined: default
        $sysViews = $this->browserViewService->getSystemViews(ObjectTypes::TASK, $this->account->getAccountId());
        $this->assertTrue(count($sysViews) >= 1);
        $this->assertInstanceOf(BrowserView::class, $sysViews[0]);

        // We know the 1st view in data/browser_views/task.php has a condition
        $conditions = $sysViews[1]->getConditions();
        $this->assertEquals("owner_id", $conditions[0]->fieldName);
    }

    /**
     * Make sure that getting account views will not return team or user views
     */
    public function testGetAccountViews()
    {
        // Setup team vuew
        $teamView = new BrowserView();
        $teamView->setObjType(ObjectTypes::NOTE);
        $teamView->setTeamId(self::TEST_TEAM_ID);
        $this->browserViewService->saveView($teamView, $this->account->getAccountId());
        $this->testViews[] = $teamView;

        // Setup user view
        $userView = new BrowserView();
        $userView->setObjType(ObjectTypes::NOTE);
        $userView->setOwnerId(self::TEST_USER_ID);
        $this->browserViewService->saveView($userView, $this->account->getAccountId());
        $this->testViews[] = $userView;

        // Set global account view
        $accountView  = new BrowserView();
        $accountView->setObjType(ObjectTypes::NOTE);
        $this->browserViewService->saveView($accountView, $this->account->getAccountId());
        $this->testViews[] = $accountView;

        // Make sure getting accounts views does not return user or team views
        $accountViews = $this->browserViewService->getAccountViews(ObjectTypes::NOTE);
        $foundUserView = false;
        $foundTeamView = false;
        foreach ($accountViews as $view) {
            if ($view->getOwnerId()) {
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
        $teamView->setTeamId(self::TEST_TEAM_ID);
        $this->browserViewService->saveView($teamView, $this->account->getAccountId());
        $this->testViews[] = $teamView;

        // Setup user view
        $userView = new BrowserView();
        $userView->setObjType(ObjectTypes::NOTE);
        $userView->setOwnerId(self::TEST_USER_ID);
        $this->browserViewService->saveView($userView, $this->account->getAccountId());
        $this->testViews[] = $userView;

        // Set global account view
        $accountView  = new BrowserView();
        $accountView->setObjType(ObjectTypes::NOTE);
        $this->browserViewService->saveView($accountView, $this->account->getAccountId());
        $this->testViews[] = $accountView;

        // Make sure getting accounts views does not return user or team views
        $teamViews = $this->browserViewService->getTeamViews(ObjectTypes::NOTE, self::TEST_TEAM_ID);
        $foundUserView = false;
        $foundAccountView = false;
        foreach ($teamViews as $view) {
            if ($view->getOwnerId()) {
                $foundUserView = true;
            }
            if (empty($view->getTeamId()) && empty($view->getOwnerId())) {
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
        $teamView->setTeamId(self::TEST_TEAM_ID);
        $this->browserViewService->saveView($teamView, $this->account->getAccountId());
        $this->testViews[] = $teamView;

        // Setup user view
        $userView = new BrowserView();
        $userView->setObjType(ObjectTypes::NOTE);
        $userView->setOwnerId(self::TEST_USER_ID);
        $this->browserViewService->saveView($userView, $this->account->getAccountId());
        $this->testViews[] = $userView;

        // Set global account view
        $accountView  = new BrowserView();
        $accountView->setObjType(ObjectTypes::NOTE);
        $this->browserViewService->saveView($accountView, $this->account->getAccountId());
        $this->testViews[] = $accountView;

        // Make sure getting accounts views does not return user or team views
        $userViews = $this->browserViewService->getUserViews(ObjectTypes::NOTE, self::TEST_USER_ID);
        $foundTeamView = false;
        $foundAccountView = false;
        foreach ($userViews as $view) {
            if ($view->getTeamid()) {
                $foundTeamView = true;
            }
            if (empty($view->getTeamId()) && empty($view->getOwnerId())) {
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
            $this->user->setValue("team_id", self::TEST_TEAM_ID);
        }

        // Setup team view
        $teamView = new BrowserView();
        $teamView->setObjType(ObjectTypes::NOTE);
        $teamView->setTeamId($this->user->getValue("team_id"));
        $this->browserViewService->saveView($teamView, $this->account->getAccountId());
        $this->testViews[] = $teamView;

        // Setup user view
        $userView = new BrowserView();
        $userView->setObjType(ObjectTypes::NOTE);
        $userView->setOwnerId($this->user->getEntityId());
        $this->browserViewService->saveView($userView, $this->account->getAccountId());
        $this->testViews[] = $userView;

        // Set global account view
        $accountView  = new BrowserView();
        $accountView->setObjType(ObjectTypes::NOTE);
        $this->browserViewService->saveView($accountView, $this->account->getAccountId());
        $this->testViews[] = $accountView;

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

    /**
     * Make sure that the task browser views are using status_id instead of "is_closed" field
     */
    public function testTaskSystemBrowserViewsStatus()
    {
        $systemViews = $this->browserViewService->getSystemViews(ObjectTypes::TASK, $this->account->getAccountId());

        $foundDoneField = false;
        foreach ($systemViews as $sysView) {
            foreach ($sysView->getConditions() as $cond) {
                // If we have found a "is_closed" field in the conditions, then
                if ($cond->fieldName == 'is_closed') {
                    $foundDoneField = true;
                }

                // Make sure that status id value is already converted to the group's guid
                if ($cond->fieldName == "status_id") {
                    $this->assertTrue(Uuid::isValid($cond->value));
                }
            }
        }

        $this->assertFalse($foundDoneField);
    }

    /**
     * Make sure that the incomplete task browser view is using status_id != completed in its conditions
     */
    public function testTaskSystemBrowserViewIncompleteTask()
    {
        $systemViews = $this->browserViewService->getSystemViews(ObjectTypes::TASK, $this->account->getAccountId());

        $foundStatusField = false;
        foreach ($systemViews as $sysView) {
            if ($sysView->getId() == "my_tasks") {
                foreach ($sysView->getConditions() as $cond) {
                    if ($cond->fieldName == "status_id") {
                        // Make sure that the value is already converted to the group's guid
                        $this->assertTrue(Uuid::isValid($cond->value));
                        $foundStatusField = true;
                        break 2;
                    }
                }
            }
        }

        $this->assertTrue($foundStatusField);
    }
}
