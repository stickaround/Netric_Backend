<?php

declare(strict_types = 1);

namespace NetricTest\Authentication;

use PHPUnit\Framework\TestCase;
use Netric\Authentication\AuthenticationIdentity;
use Ramsey\Uuid\Uuid;

class AuthenticationIdentityTest extends TestCase
{
    public function testGetUserId()
    {
        // Create some fake IDs to test
        $userId = Uuid::uuid4()->toString();
        $accountId = Uuid::uuid4()->toString();
        $identity = new AuthenticationIdentity($accountId, $userId);
        $this->assertEquals($userId, $identity->getUserId());
    }

    public function testGetAccountId()
    {
        // Create some fake IDs to test
        $userId = Uuid::uuid4()->toString();
        $accountId = Uuid::uuid4()->toString();
        $identity = new AuthenticationIdentity($accountId, $userId);
        $this->assertEquals($accountId, $identity->getAccountId());
    }
}