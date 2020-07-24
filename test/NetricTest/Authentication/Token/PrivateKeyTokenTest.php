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

    protected function setUp(): void
    {
        $this->entityLoaderMock = $this->createStub(EntityLoader::class);
        $entityMock = $this->createStub(EntityInterface::class);
        $entityMock->method('getEntityId')->willReturn(self::TEST_UUID);
        $this->entityLoaderMock->method('getByUniqueName')->willReturn($entityMock);
    }
    public function testTokenIsValid(): void
    {
        $token = new PrivateKeyToken("SECRET", "ACCOUNT:SECRET", $this->entityLoaderMock);
        $this->assertTrue($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenBlank()
    {
        $token = new PrivateKeyToken("", "", $this->entityLoaderMock);
        $this->assertFalse($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenMismatch()
    {
        $token = new PrivateKeyToken("SECRET", "BLAH", $this->entityLoaderMock);
        $this->assertFalse($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenNoAccount()
    {
        $token = new PrivateKeyToken("SECRET", "SECRET", $this->entityLoaderMock);
        $this->assertFalse($token->tokenIsValid());
    }

    public function testGetUserId(): void
    {
        $token = new PrivateKeyToken("SECRET", "ACCOUNT:SECRET", $this->entityLoaderMock);
        $this->assertEquals(self::TEST_UUID, $token->getUserGuid());
    }

    public function testGetAccountId(): void
    {
        $token = new PrivateKeyToken("SECRET", "ACCOUNT:SECRET", $this->entityLoaderMock);
        $this->assertEquals('ACCOUNT', $token->getAccountId());
    }
}
