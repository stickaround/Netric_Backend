<?php
namespace Netric\Authentication;

/**
 * Return identity from authentication system
 */
class AuthenticationIdentity
{
    /**
     * Unique ID of authenticated user
     *
     * @var string
     */
    private $userId = "";

    /**
     * Unique account id of authenticated user
     *
     * @var string
     */
    private $accountId = "";

    /**
     * AuthenticationIdentity constructor.
     *
     * @param string $accountId
     * @param string $userId
     */
    public function __construct(string $accountId, string $userId)
    {
        $this->accountId = $accountId;
        $this->userId = $userId;
    }

    /**
     * Get the unique id of the authenticated user
     *
     * @return string
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * Get the account id of the authenticated user
     *
     * @return string
     */
    public function getAccountId(): string
    {
        return $this->accountId;
    }
}
