<?php
/**
 * Test the authentication service
 */
namespace NetricTest\Authentication;

use Netric;
use PHPUnit\Framework\TestCase;
use Netric\Authentication\AuthenticationService;
use Netric\Request\RequestInterface;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Entity\DataMapper\DataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;

/**
 * @group integration
 */
class AuthenticationServiceTest extends TestCase
{

    /**
     * Account used for testing
     *
     * @var \Netric\Account\Account
     */
    protected $account = null;

    /**
     * Auth service mocked out
     *
     * @var \Netric\Authentication\AuthenticationService
     */
    private $authService = null;

    /**
     * Test user
     *
     * @var \Netric\Entity\ObjType\UserEntity
     */
    private $user = null;

    /**
     * Common constants used
     *
     * @cons string
     */
    const TEST_USER = "test_auth";
    const TEST_USER_PASS = "testpass";

    protected function setUp()
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->authService = $this->account->getServiceManager()->get(AuthenticationServiceFactory::class);
        
        // Setup entity datamapper for handling users
        $dm = $this->account->getServiceManager()->get(DataMapperFactory::class);

        // Make sure old test user does not exist
        $query = new \Netric\EntityQuery(ObjectTypes::USER);
        $query->where('name')->equals(self::TEST_USER);
        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $res = $index->executeQuery($query);
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $user = $res->getEntity($i);
            $dm->delete($user, true);
        }

        // Create a test user
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $user = $loader->create(ObjectTypes::USER);
        $user->setValue("name", self::TEST_USER);
        $user->setValue("password", self::TEST_USER_PASS);
        $user->setValue("active", true);
        $dm->save($user);
        $this->user = $user;
    }

    protected function tearDown()
    {
        if ($this->user) {
            $dm = $this->account->getServiceManager()->get(DataMapperFactory::class);
            $dm->delete($this->user, true);
        }
    }

    public function testHashPassword()
    {
        $salt = "MYTESTSALT";
        $password = self::TEST_USER_PASS;

        // Make sure the hash is repeatable
        $orig = $this->authService->hashPassword($password, $salt);

        // Make sure the same password works
        $samePass = $this->authService->hashPassword($password, $salt);
        $this->assertEquals($orig, $samePass);

        // Make sure that a different salt fails
        $notSamePass = $this->authService->hashPassword($password, "notsame");
        $this->assertNotEquals($orig, $notSamePass);

        // Make sure that a different password fails
        $notSamePass = $this->authService->hashPassword("testdiff", $salt);
        $this->assertNotEquals($orig, $notSamePass);
    }

    public function testGenerateSalt()
    {
        $first = $this->authService->generateSalt();
        $second = $this->authService->generateSalt();
        $this->assertNotEquals($first, $second);
    }

    /**
     * Test authenticate with good credentials
     */
    public function testAuthenticate()
    {
        // TEST_USER was created in $this->setUp
        $ret = $this->authService->authenticate(self::TEST_USER, self::TEST_USER_PASS);
        
        // Make sure we got a session string back
        $this->assertNotNull($ret);

        // Make sure the validated identntiy was cached (since the session header is not set)
        $this->assertEquals($this->user->getId(), $this->authService->getIdentity());
    }

    public function testAuthenticateBadUser()
    {
        $ret = $this->authService->authenticate(self::TEST_USER . "_bad", self::TEST_USER_PASS);
        $this->assertNull($ret);
    }

    public function testAuthenticateBadPass()
    {
        $ret = $this->authService->authenticate(self::TEST_USER, self::TEST_USER_PASS . "_bad");
        $this->assertNull($ret);
    }

    public function testGetSignedSession()
    {
        $userId = 1;
        $expires = -1; // Never
        $pass = self::TEST_USER_PASS;
        $salt = "testsalt";

        $sessionStr = $this->authService->getSignedSession($userId, $expires, $pass, $salt);
        $parts = explode(":", $sessionStr);
        $sessUid = $parts[AuthenticationService::SESSIONPART_USERID];
        $sessExp = $parts[AuthenticationService::SESSIONPART_EXPIRES];
        $sessPwd = $parts[AuthenticationService::SESSIONPART_PASSWORD];
        $sessSgn = $parts[AuthenticationService::SESSIONPART_SIGNATURE];

        // Setup a reflection object to test validation which is private
        $refAuthService = new \ReflectionObject($this->authService);
        $sessionSignatureIsValid = $refAuthService->getMethod("sessionSignatureIsValid");
        $sessionSignatureIsValid->setAccessible(true);

        // Simulate validating an unchanged session
        $ret = $sessionSignatureIsValid->invoke($this->authService, $sessUid, $sessExp, $sessPwd, $sessSgn);
        $this->assertTrue($ret);

        // Pretend the client tampered with the expires param
        $sessExp = 0;
        $ret = $sessionSignatureIsValid->invoke($this->authService, $sessUid, $sessExp, $sessPwd, $sessSgn);
        $this->assertFalse($ret);
        $sessExp = $parts[AuthenticationService::SESSIONPART_EXPIRES];

        // Pretend the client tries to chage the user id
        $sessUid = 100;
        $ret = $sessionSignatureIsValid->invoke($this->authService, $sessUid, $sessExp, $sessPwd, $sessSgn);
        $this->assertFalse($ret);
    }

    public function testGetIdentity()
    {
        $expires = -1; // Never
        $pass = self::TEST_USER_PASS;
        $salt = "testsalt";

        $sessionStr = $this->authService->getSignedSession($this->user->getId(), $expires, $pass, $salt);

        // Create a mock request and pretend $sessionStr was in the 'Authenticaton' header field
        $req = $this->getMockBuilder(RequestInterface::class)
                     ->getMock();
        $req->method('getParam')->willReturn($sessionStr);
        $this->authService->setRequest($req);

        // Now get the identity (userid) of the authenticated user
        $authUserId = $this->authService->getIdentity();
        $this->assertEquals($this->user->getId(), $authUserId);
    }
}
