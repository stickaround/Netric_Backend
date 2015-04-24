<?php
/**
 * Test calling the authentication controller
 */
namespace NetricTest\Controller;

use Netric;
use PHPUnit_Framework_TestCase;

class AuthenticationControllerTest extends PHPUnit_Framework_TestCase 
{   
    /**
     * Account used for testing
     *
     * @var \Netric\Account
     */
    protected $account = null;

    /**
     * Controller instance used for testing
     *
     * @var \Netric\Controller\AuthenticationController
     */
    protected $controller = null;

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();

        // Setup a user for testing
        $loader = $this->account->getServiceManager()->get("EntityLoader");
        $user = $loader->get("user", \Netric\Entity\ObjType\User::USER_ADMINISTRATOR);
        $this->account->setCurrentUser($user);

        // Create the controller
        $this->controller = new Netric\Controller\AuthenticationController($this->account);
        $this->controller->testMode = true;
    }
}