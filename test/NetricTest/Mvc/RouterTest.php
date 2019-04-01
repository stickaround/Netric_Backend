<?php
namespace NetricTest\Mvc;

use Netric;
use PHPUnit\Framework\TestCase;
use Netric\Request\HttpRequest;
use Netric\Request\ConsoleRequest;
use Netric\Entity\ObjType\UserEntity;
use Netric\Request\RequestFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\Mvc\Exception\NotAuthorizedForRouteException;

/**
 * Test querying ElasticSearch server
 *
 * Most tests are inherited from IndexTestsAbstract.php.
 * Only define index specific tests here and try to avoid name collision with the tests
 * in the parent class. For the most part, the parent class tests all public functions
 * so private functions should be tested below.
 */
class RouterTest extends TestCase
{
    public function testRun()
    {
        $account = \NetricTest\Bootstrap::getAccount();

        $request = new HttpRequest();
        $request->setParam("controller", "test");
        $request->setParam("function", "test");

        $svr = new Netric\Mvc\Router($account->getApplication());
        $svr->testMode = true;
        $ret = $svr->run($request);
        $this->assertEquals(['param'=>'test'], $ret->getOutputBuffer());
    }

    /**
     * Make sure that an unauthorized user cannot get to a route
     */
    public function testAccessControlFail()
    {
        $account = \NetricTest\Bootstrap::getAccount();

        // Setup anonymous user which should be blocked
        $origCurrentUser = $account->getUser();
        $loader = $account->getServiceManager()->get(EntityLoaderFactory::class);
        $user = $loader->getByGuid(UserEntity::USER_ANONYMOUS);
        $account->setCurrentUser($user);

        $request = new HttpRequest();
        $request->setParam("controller", "test");
        $request->setParam("function", "test");

        $svr = new Netric\Mvc\Router($account->getApplication());
        $svr->testMode = true;

        $this->expectException(NotAuthorizedForRouteException::class);
        $svr->run($request);

        // Restore original
        $account->setCurrentUser($origCurrentUser);
    }

    /**
     * The console request should be allowed to call anything
     */
    public function testAccessControl_Console()
    {
        $account = \NetricTest\Bootstrap::getAccount();

        // Setup anonymous user which should be blocked
        $origCurrentUser = $account->getUser();
        $loader = $account->getServiceManager()->get(EntityLoaderFactory::class);
        $user = $loader->getByGuid(UserEntity::USER_ANONYMOUS);
        $account->setCurrentUser($user);

        $request = new ConsoleRequest();
        $request->setParam("controller", "test");
        $request->setParam("function", "test");

        $svr = new Netric\Mvc\Router($account->getApplication());
        $svr->testMode = true;
        $ret = $svr->run($request);
        // Request should fail succeed even though we do not have an authenticated user
        $this->assertEquals($ret->getOutputBuffer(), ['test']);

        // Restore original
        $account->setCurrentUser($origCurrentUser);
    }
}
