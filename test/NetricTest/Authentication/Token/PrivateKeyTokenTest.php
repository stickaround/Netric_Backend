<?php
namespace NetricTest\Authentication\Token;

use Netric\Authentication\Token\PrivateKeyToken;
use Netric\Entity\ObjType\UserEntity;
use PHPUnit\Framework\TestCase;

/**
 * Make sure private key tokens work
 */
class PrivateKeyTokenTest extends TestCase
{
    public function testTokenIsValid(): void
    {
        $token = new PrivateKeyToken("SECRET", "SECRET");
        $this->assertTrue($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenBlank()
    {
        $token = new PrivateKeyToken("", "");
        $this->assertFalse($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenMismatch()
    {
        $token = new PrivateKeyToken("SECRET", "");
        $this->assertFalse($token->tokenIsValid());
    }

    public function testGetUserGuid(): void
    {
        $token = new PrivateKeyToken("SECRET", "SECRET");
        $this->assertEquals(UserEntity::USER_SYSTEM, $token->getUserGuid());
    }
}
