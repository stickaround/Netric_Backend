<?php
namespace Netric\Authentication\Token;

/**
 * Token based on HMAC and roughly copied from amazon S3 design.
 */
class HmacToken implements AuthenticationTokenInterface
{
    /**
     * Authentication token
     *
     * @var string
     */
    private $authToken = "";

    /**
     * HmacToken constructor.
     *
     * @param string $authToken
     */
    public function __construct(string $authToken)
    {
        $this->authToken = $authToken;
    }

    /**
     * Check if a token is valid
     *
     * @return bool
     */
    public function tokenIsValid(): bool
    {
        return false;
    }

    /**
     * Get GUID for the user of a token
     *
     * @return string
     */
    public function getUserGuid(): string
    {
        return "";
    }
}
