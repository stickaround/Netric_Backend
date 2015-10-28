<?php
/**
 * Test a browser view object
 */
namespace NetricTest\Entity\BrowserView;

use Netric\Entity\BrowserView;
use PHPUnit_Framework_TestCase;

class BrowserViewTest extends PHPUnit_Framework_TestCase
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
     * @var \Netric\Entity\Form
     */
    private $formService = null;

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
        $this->formService = $sm->get("Netric/Entity/Forms");
        $this->user = $this->account->getUser(\Netric\User::USER_ADMINISTRATOR);
    }

    public function testToArray()
    {
        // TODO: test
    }

    public function testFromArray()
    {
        // TODO: test
    }
}