<?php

namespace Netric\Authentication\Token;

use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\ObjectTypes;

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
     * Load users
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * HmacToken constructor.
     *
     * @param string $privateKey
     * @param string $authToken
     * @param Entityloader $entityLoader Used to get users
     */
    public function __construct(string $privateKey, string $authToken, EntityLoader $entityLoader)
    {
        $this->privateKey = $privateKey;
        $this->authToken = $authToken;
        $this->entityLoader = $entityLoader;
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

        // Split the token
        $tokenParts = explode(':', $this->authToken);
        if (count($tokenParts) != 2) {
            return false;
        }

        $tokenKey = $tokenParts[1];

        // It is only valid if the keys match
        return ($this->privateKey === $tokenKey);
    }

    /**
     * Get GUID for the system user if the token is valid
     *
     * @return string
     */
    public function getUserId(): string
    {
        if (!$this->tokenIsValid()) {
            return "";
        }

        // TODO: Return something here so that we know to pull the system user
        // // Load the system user and return it
        // $systemUser = $this->entityLoader->getByUniqueName(ObjectTypes::USER, UserEntity::USER_SYSTEM);
        // return $systemUser->getEntityId();
    }

    /**
     * Get the account ID for this user
     *
     * @return string
     */
    public function getAccountId(): string
    {
        if (!$this->tokenIsValid()) {
            return "";
        }

        $tokenParts = explode(':', $this->authToken);
        return $tokenParts[0];
    }

    /**
     * Generate a token that can be used to verify the authenticity of a request
     *
     * @param UserEntity $user
     * @return string
     */
    public function createToken(UserEntity $user): string
    {
        return '';
    }
}
