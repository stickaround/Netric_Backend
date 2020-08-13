<?php

namespace NetricTest\Authentication;

use Netric;
use PHPUnit\Framework\TestCase;
use Netric\Authentication\AuthenticationService;
use Netric\Request\RequestInterface;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\Entity\DataMapper\EntityDataMapperFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Entity\EntityLoaderFactory;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\EntityQuery;
use ReflectionException;

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

    /**
     * Example private key to use for NTRC-PVK method
     *
     * @const string
     */
    const PRIVATE_KEY = "427846f9-4490-488e-b6dc-cc059297de0f";

    protected function setUp(): void
    {
        $this->account = \NetricTest\Bootstrap::getAccount();
        $this->authService = $this->account->getServiceManager()->get(AuthenticationServiceFactory::class);
        $this->authService->setPrivateKey(self::PRIVATE_KEY);

        // Setup entity datamapper for handling users
        $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);

        // Make sure old test user does not exist
        $query = new EntityQuery(ObjectTypes::USER, $this->account->getAccountId());
        $query->where('name')->equals(self::TEST_USER);
        $index = $this->account->getServiceManager()->get(IndexFactory::class);
        $res = $index->executeQuery($query);
        for ($i = 0; $i < $res->getTotalNum(); $i++) {
            $user = $res->getEntity($i);
            $dm->delete($user, $this->account->getAuthenticatedUser());
        }

        // Create a test user
        $loader = $this->account->getServiceManager()->get(EntityLoaderFactory::class);
        $user = $loader->create(ObjectTypes::USER, $this->account->getAccountId());
        $user->setValue("name", self::TEST_USER);
        $user->setValue('account_id', $this->account->getAccountId());
        $user->setValue("password", self::TEST_USER_PASS);
        $user->setValue("active", true);
        $dm->save($user, $this->account->getSystemUser());
        $this->user = $user;
    }

    protected function tearDown(): void
    {
        if ($this->user) {
            $dm = $this->account->getServiceManager()->get(EntityDataMapperFactory::class);
            $dm->delete($this->user, $this->account->getAuthenticatedUser());
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

    /**
     * Test authenticate with good credentials
     */
    public function testAuthenticate()
    {
        // TEST_USER was created in $this->setUp
        $sessionToken = $this->authService->authenticate(
            self::TEST_USER,
            self::TEST_USER_PASS,
            $this->account->getName()
        );

        // Make sure we got a session string back
        $this->assertNotNull($sessionToken);

        // Make sure the validated identntiy was cached (since the session header is not set)
        $this->assertEquals($this->user->getEntityId(), $this->authService->getIdentity()->getUserId());
    }

    /**
     * Make sure that invalid users do not get a token
     *
     * @return void
     */
    public function testAuthenticateBadUser()
    {
        $ret = $this->authService->authenticate(
            self::TEST_USER . "_bad",
            self::TEST_USER_PASS,
            $this->account->getName()
        );
        $this->assertNull($ret);
    }

    /**
     * Make sure bad passwords do not get a token
     *
     * @return void
     */
    public function testAuthenticateBadPass()
    {
        $ret = $this->authService->authenticate(
            self::TEST_USER,
            self::TEST_USER_PASS . "_bad",
            $this->account->getName()
        );
        $this->assertNull($ret);
    }

    /**
     * @deprecated We should use JWT now
     *
     * @return void
     */
    // public function testGetSignedSession()
    // {
    //     $userId = 1;
    //     $expires = -1; // Never
    //     $pass = self::TEST_USER_PASS;
    //     $salt = "testsalt";

    //     $sessionStr = $this->authService->getSignedSession($userId, $expires, $pass, $salt);
    //     $parts = explode(":", $sessionStr);
    //     $sessUid = $parts[AuthenticationService::SESSIONPART_USERID];
    //     $sessExp = $parts[AuthenticationService::SESSIONPART_EXPIRES];
    //     $sessPwd = $parts[AuthenticationService::SESSIONPART_PASSWORD];
    //     $sessSgn = $parts[AuthenticationService::SESSIONPART_SIGNATURE];

    //     // Setup a reflection object to test validation which is private
    //     $refAuthService = new \ReflectionObject($this->authService);
    //     $sessionSignatureIsValid = $refAuthService->getMethod("sessionSignatureIsValid");
    //     $sessionSignatureIsValid->setAccessible(true);

    //     // Simulate validating an unchanged session
    //     $ret = $sessionSignatureIsValid->invoke($this->authService, $sessUid, $sessExp, $sessPwd, $sessSgn);
    //     $this->assertTrue($ret);

    //     // Pretend the client tampered with the expires param
    //     $sessExp = 0;
    //     $ret = $sessionSignatureIsValid->invoke($this->authService, $sessUid, $sessExp, $sessPwd, $sessSgn);
    //     $this->assertFalse($ret);
    //     $sessExp = $parts[AuthenticationService::SESSIONPART_EXPIRES];

    //     // Pretend the client tries to chage the user id
    //     $sessUid = 100;
    //     $ret = $sessionSignatureIsValid->invoke($this->authService, $sessUid, $sessExp, $sessPwd, $sessSgn);
    //     $this->assertFalse($ret);
    // }

    // public function testGetIdentity()
    // {
    //     $expires = -1; // Never
    //     $pass = self::TEST_USER_PASS;
    //     $salt = "testsalt";

    //     $sessionStr = $this->authService->getSignedSession($this->user->getEntityId(), $expires, $pass, $salt);

    //     // Create a mock request and pretend $sessionStr was in the 'Authentication' header field
    //     $req = $this->getMockBuilder(RequestInterface::class)
    //         ->getMock();
    //     $req->method('getParam')->willReturn($sessionStr);
    //     $this->authService->setRequest($req);

    //     // Now get the identity (userid) of the authenticated user
    //     $authUserId = $this->authService->getIdentity();
    //     $this->assertEquals($this->user->getEntityId(), $authUserId);
    // }

    public function testGetAuthenticatedUser()
    {
        // Create a valid token to test
        $sessionToken = $this->authService->authenticate(
            self::TEST_USER,
            self::TEST_USER_PASS,
            $this->account->getName()
        );

        // Create a mock request and pretend $sessionStr was in the 'Authentication' header field
        $req = $this->getMockBuilder(RequestInterface::class)->getMock();
        $req->method('getParam')->willReturn($sessionToken);
        $this->authService->setRequest($req);

        // Now get the identity (userid) of the authenticated user
        $identity = $this->authService->getIdentity();
        $this->assertEquals($this->user->getEntityId(), $identity->getUserId());
        $this->assertEquals($this->user->getValue('account_id'), $identity->getAccountId());
    }

    /**
     * Make sure we can get an identity from a shared private key
     *
     * This is used for service-to-service calls only. The private key
     * will never be shared outside of our service network.
     */
    // public function testGetIdentityPrivateKey()
    // {
    //     $authHeader = AuthenticationService::METHOD_PRIVATE_KEY .
    //         " " . $this->account->getAccountId() . ':' . self::PRIVATE_KEY;

    //     // Create a mock request and pretend $sessionStr was in the 'Authentication' header field
    //     $req = $this->getMockBuilder(RequestInterface::class)->getMock();
    //     $req->method('getParam')->willReturn($authHeader);
    //     $this->authService->setRequest($req);

    //     // Make sure the system user was loaded
    //     $authUserId = $this->authService->getIdentity();
    //     $this->assertNotNull($authUserId, "UserId for system user not found");
    // }

    /**
     * Verify that a bad private key will result in no identity being returned
     */
    // public function testGetIdentityPrivateKeyWithBadKey()
    // {
    //     $authHeader = AuthenticationService::METHOD_PRIVATE_KEY . " BADKEY";

    //     // Create a mock request and pretend $sessionStr was in the 'Authentication' header field
    //     $req = $this->getMockBuilder(RequestInterface::class)->getMock();
    //     $req->method('getParam')->willReturn($authHeader);
    //     $this->authService->setRequest($req);

    //     // Make sure the system user was loaded
    //     $authUserId = $this->authService->getIdentity();
    //     $this->assertNull($authUserId);
    // }
}
