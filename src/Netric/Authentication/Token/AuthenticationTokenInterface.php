<?php

namespace Netric\Authentication\Token;

use Netric\Entity\ObjType\UserEntity;

interface AuthenticationTokenInterface
{
    /**
     * Check if a token is valid
     *
     * @return bool
     */
    public function tokenIsValid(): bool;

    /**
     * Get GUID for the user of a token
     *
     * @return string
     */
    public function getUserId(): string;

    /**
     * Get the account ID for this user
     *
     * @return string
     */
    public function getAccountId(): string;

    /**
     * Generate a token that can be used to verify the authenticity of a request
     *
     * @param UserEntity $user
     * @return string
     */
    public function createToken(UserEntity $user): string;
}
