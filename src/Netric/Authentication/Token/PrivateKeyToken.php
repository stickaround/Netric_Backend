<?php
namespace Netric\Authentication\Token;

use Netric\Entity\ObjType\UserEntity;

/**
 * Token based on a private key.
 *
 * This should only ever be used for internal service calls and never
 * be exposed to public APIs.
 */
class PrivateKeyToken implements AuthenticationTokenInterface
{
    /**
     * Authentication token
     *
     * @var string
     */
    private $authToken = "";

    /**
     * Shared private key
     *
     * @var string
     */
    private $privateKey = "";

    /**
     * HmacToken constructor.
     *
     * @param string $privateKey
     * @param string $authToken
     */
    public function __construct(string $privateKey, string $authToken)
    {
        $this->privateKey = $privateKey;
        $this->authToken = $authToken;
    }

    /**
     * Check if a token is valid
     *
     * @return bool
     */
    public function tokenIsValid(): bool
    {
        if (!$this->privateKey || !$this->authToken) {
            return false;
        }

        // It is only valid if the keys match
        return ($this->privateKey === $this->authToken);
    }

    /**
     * Get GUID for the system user if the token is valid
     *
     * @return string
     */
    public function getUserGuid(): string
    {
        if (!$this->tokenIsValid()) {
            return "";
        }

        return UserEntity::USER_SYSTEM;
    }
}
