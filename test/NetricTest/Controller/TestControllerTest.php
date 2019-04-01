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

use PHPUnit\Framework\TestCase;
use Netric\Controller\TestController;
use Netric\Request\HttpRequest;

class TestControllerTest extends TestCase
{
    /**
     * Test automatic pagination
     */
    public function testGetTestAction()
    {
        $con = new TestController();
        $ret = $con->getTestAction(new HttpRequest());
        $this->assertEquals(['param'=>'test'], $ret->getOutputBuffer());
    }
}
