<?php

namespace NetricTest\Authentication\Token;

use Netric\Authentication\Token\PrivateKeyToken;
use Netric\Entity\EntityInterface;
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoader;
use PHPUnit\Framework\TestCase;

/**
 * Make sure private key tokens work
 */
class PrivateKeyTokenTest extends TestCase
{
    /**
     * Mock entity loader used to get the system user
     *
     * @return EntityLoader
     */
    private $entityLoaderMock = null;

    /**
     * Used for testing return ID of user
     */
    const TEST_UUID = '592536ad-fc91-499a-8e2c-79a92e1caa91';

    public function testTokenIsValid(): void
    {
        $token = new PrivateKeyToken("SECRET", "ACCOUNT:SECRET");
        $this->assertTrue($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenBlank()
    {
        $token = new PrivateKeyToken("", "");
        $this->assertFalse($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenMismatch()
    {
        $token = new PrivateKeyToken("SECRET", "BLAH");
        $this->assertFalse($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenNoAccount()
    {
        $token = new PrivateKeyToken("SECRET", "SECRET");
        $this->assertFalse($token->tokenIsValid());
    }

    public function testGetUserId(): void
    {
        $token = new PrivateKeyToken("SECRET", "ACCOUNT:SECRET");
        $this->assertEquals(UserEntity::USER_SYSTEM, $token->getUserId());
    }

    public function testGetAccountId(): void
    {
        $token = new PrivateKeyToken("SECRET", "ACCOUNT:SECRET");
        $this->assertEquals('ACCOUNT', $token->getAccountId());
    }

    /**
     * Make sure you cannot create a token with private keys
     *
     * @return void
     */
    public function testCreateToken(): void
    {
        $entityMock = $this->createStub(UserEntity::class);
        $token = new PrivateKeyToken("SECRET", "ACCOUNT:SECRET");
        $this->assertEmpty($token->createToken($entityMock));
    }
}
