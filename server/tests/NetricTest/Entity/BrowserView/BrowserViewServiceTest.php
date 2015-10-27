<?php
/**
 * Test the browser view service for getting browser views for a user
 */
namespace NetricTest\Entity\BrowserView;

use Netric\Entity\BrowserView;
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

    // TODO: Test here
}
