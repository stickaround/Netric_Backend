<?php

namespace NetricTest\Authentication\Token;

use Netric\Authentication\Token\JsonWebToken;
use Netric\Entity\ObjType\UserEntity;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Make sure json-web-tokens work
 */
class JsonWebTokenTest extends TestCase
{
    /**
     * Used for testing return ID of user
     */
    const TEST_UUID = '592536ad-fc91-499a-8e2c-79a92e1caa91';
    const TEST_ACCOUNT_ID = 'gfdgesdad-fc91-499a-8e2c-79a92e1caa91';

    /**
     * Create a test private key
     */
    const TEST_PRIVATE_KEY = "fdsKJHhgfdjhgfdkjhjdhwohfoduhsgkfjdh";

    /**
     * Mock user
     *
     * @var UserEntity
     */
    private $mockUserEntity = null;

    /**
     * Setup the test
     *
     * @return void
     */
    protected function setUp(): void
    {
        $entityMock = $this->createStub(UserEntity::class);
        $entityMock->method('getEntityId')->willReturn(self::TEST_UUID);
        $entityMock->method('getName')->willReturn('test');

        // First param is the field_name, second is what gets returned
        $mockReturnMap = [
            ['account_id', self::TEST_ACCOUNT_ID],
            ['email', 'test@test.com']
        ];

        // Configure the stub.
        $entityMock->method('getValue')->will($this->returnValueMap($mockReturnMap));

        $this->mockUserEntity = $entityMock;
    }

    public function testTokenIsValid(): void
    {
        // Create a test token
        $token = new JsonWebToken(self::TEST_PRIVATE_KEY, "");
        $encodedToken = $token->createToken($this->mockUserEntity);

        $token = new JsonWebToken(self::TEST_PRIVATE_KEY,  $encodedToken);
        $this->assertTrue($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenBlank()
    {
        $token = new JsonWebToken("", "");
        $this->assertFalse($token->tokenIsValid());
    }

    public function testTokenIsValidFailsWhenMismatch()
    {
        $token = new JsonWebToken(self::TEST_PRIVATE_KEY, "BLAH");
        $this->assertFalse($token->tokenIsValid());
    }

    public function testGetUserId(): void
    {
        // Create a test token
        $token = new JsonWebToken(self::TEST_PRIVATE_KEY, "");
        $encodedToken = $token->createToken($this->mockUserEntity);

        // Extract account id from encoded token
        $token = new JsonWebToken(self::TEST_PRIVATE_KEY, $encodedToken);
        $this->assertEquals(self::TEST_UUID, $token->getUserId());
    }

    public function testGetAccountId(): void
    {
        // Create a test token
        $token = new JsonWebToken(self::TEST_PRIVATE_KEY, "");
        $encodedToken = $token->createToken($this->mockUserEntity);

        // Extract account id from encoded token
        $token = new JsonWebToken(self::TEST_PRIVATE_KEY, $encodedToken);
        $this->assertEquals(self::TEST_ACCOUNT_ID, $token->getAccountId());
    }

    public function testCreateToken(): void
    {
        $token = new JsonWebToken(self::TEST_PRIVATE_KEY, "");
        $this->assertNotEmpty($token->createToken($this->mockUserEntity));
    }

    public function testCreateTokenWithBadUserFails(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $token = new JsonWebToken(self::TEST_PRIVATE_KEY, "");
        // Should trigger exception because user_id is not set
        $token->createToken($this->createStub(UserEntity::class));
    }
}
