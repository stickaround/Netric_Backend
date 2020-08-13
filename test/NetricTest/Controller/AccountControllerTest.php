<?php

/**
 * Test the account controller
 */

namespace NetricTest\Controller;

use Netric;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\AccountController;

/**
 * @group integration
 */
class AccountControllerTest extends TestCase
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
     * @var \Netric\Controller\EntityController
     */
    protected $controller = null;

    protected function setUp(): void
    {
        $this->account = Bootstrap::getAccount();

        // Create the controller
        $this->controller = new AccountController(
            $this->account->getApplication(),
            $this->account
        );
        $this->controller->testMode = true;
    }

    public function testGetDefinitionForms()
    {

        $ret = $this->controller->getGetAction();

        // Make sure that modules that has xml_navigation
        $this->assertFalse(empty($ret['modules'][0]['navigation']));
    }
}
