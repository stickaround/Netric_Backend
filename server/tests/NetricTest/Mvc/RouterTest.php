<?php
/**
 * Test querying ElasticSearch server
 *
 * Most tests are inherited from IndexTestsAbstract.php.
 * Only define index specific tests here and try to avoid name collision with the tests
 * in the parent class. For the most part, the parent class tests all public functions
 * so private functions should be tested below.
 */
namespace NetricTest\Mvc;

use Netric;
use PHPUnit_Framework_TestCase;

class RouterTest extends PHPUnit_Framework_TestCase 
{   
    /**
     * Test automatic pagination
     */
    public function testRun()
    {
        $account = \NetricTest\Bootstrap::getAccount();
        
        // Setup a user with permissions
        $loader = $account->getServiceManager()->get("EntityLoader");
        $user = $loader->get("user", \Netric\Entity\ObjType\UserEntity::USER_ADMINISTRATOR);
        $account->setCurrentUser($user);

        $svr = new Netric\Mvc\Router($account->getApplication());
        $svr->testMode = true;
        $svr->setClass("Netric\\Controller\\TestController");
        $ret = $svr->run('test');
		$this->assertEquals($ret, "test");
    }

    public function testAccessControl()
    {
        $account = \NetricTest\Bootstrap::getAccount();

        // Setup anonymous user which should be blocked
        $loader = $account->getServiceManager()->get("EntityLoader");
        $user = $loader->get("user", \Netric\Entity\ObjType\UserEntity::USER_ANONYMOUS);
        $account->setCurrentUser($user);

        $svr = new Netric\Mvc\Router($account->getApplication());
        $svr->testMode = true;
        $svr->setClass("Netric\\Controller\\TestController");
        $ret = $svr->run('test');
        // Request should fail because test requires an authenticated user
        $this->assertEquals($ret, false);
    }
}