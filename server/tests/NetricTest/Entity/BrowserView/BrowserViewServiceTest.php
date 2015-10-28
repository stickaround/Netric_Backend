<?php
/**
 * Test the browser view service for getting browser views for a user
 */
namespace NetricTest\Entity\BrowserView;

use Netric\Entity\BrowserView\BrowserView;
use Netric\EntityQuery;
use PHPUnit_Framework_TestCase;

class BrowserViewSErviceTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tennant account
     *
     * @var \Netric\Account
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
    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $sm = $this->account->getServiceManager();
        $this->browserViewService = $sm->get("Netric/Entity/BrowserView/BrowserViewService");
        $this->user = $this->account->getUser(\Netric\User::USER_ADMINISTRATOR);
    }

    public function testSaveView()
    {
        $data = array(
            'obj_type' => 'customer',
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

    }

    public function testLoadView()
    {
        $data = array(
            'obj_type' => 'customer',
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
        $loaded = $this->browserViewService->getById("customer", $vid);
        $this->assertNotNull($loaded);
        $this->assertEquals($loaded->getObjType(), $data['obj_type']);
        $this->assertEquals(count($data['conditions']), count($view->getConditions()));
        $this->assertEquals(count($data['table_columns']), count($view->getTableColumns()));
        $this->assertEquals(count($data['order_by']), count($view->getOrderBy()));
    }
}
