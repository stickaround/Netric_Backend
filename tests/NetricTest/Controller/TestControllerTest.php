<?php

/**
 * Test querying ElasticSearch server
 *
 * Most tests are inherited from IndexTestsAbstract.php.
 * Only define index specific tests here and try to avoid name collision with the tests
 * in the parent class. For the most part, the parent class tests all public functions
 * so private functions should be tested below.
 */
namespace NetricTest\Controller;

use Netric;
use PHPUnit\Framework\TestCase;
use NetricTest\Bootstrap;
use Netric\Controller\TestController;

class TestControllerTest extends TestCase
{
    /**
     * Test automatic pagination
     */
    public function testTest()
    {
        $account = Bootstrap::getAccount();
        $con = new TestController($account->getApplication(), $account);
        $con->testMode = true;
        $ret = $con->getTestAction();
        $this->assertEquals("test", $ret);
    }
}
