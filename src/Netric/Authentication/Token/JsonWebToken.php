<?php

declare(strict_types=1);

namespace Netric\Authentication\Token;

use Netric\Entity\ObjType\UserEntity;
use Firebase\JWT\JWT;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * New token using JWT
 */
class JsonWebToken implements AuthenticationTokenInterface
{
    /**
     * Authentication token
     */
    private string $authToken = "";

    /**
     * Shared private key
     */
    private string $privateKey = "";

    /**
     * User id from token (if valid)
     */
    private string $tokenUserId = "";

    /**
     * Account id from token (if valid)
     */
    private string $tokenAccountId = "";

    /**
     * Reason a token is valid
     */
    private string $lastFailureReason = "";

    /**
     * Algorithm used for JTW tokens
     */
    const JWT_ALGORITHM = 'HS256';

    /**
     * HmacToken constructor.
     *
     * @param string $privateKey
     * @param string $authToken
     * @param Entityloader $entityLoader Used to get users
     */
    public function __construct(string $privateKey, string $authToken = "")
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
        if (!$this->authToken) {
            return false;
        }

        return $this->setParamsFromTokens();
    }

    /**
     * Get GUID for the user of a token
     *
     * @return string
     */
    public function getUserId(): string
    {
        if (!$this->setParamsFromTokens()) {
            return '';
        }

        return $this->tokenUserId;
    }

    /**
     * Get the account ID for this user
     *
     * @return string
     */
    public function getAccountId(): string
    {
        if (!$this->setParamsFromTokens()) {
            return '';
        }

        return $this->tokenAccountId;
    }

    /**
     * Generate a token that can be used to verify the authenticity of a request
     *
     * @param UserEntity $user
     * @return string
     */
    public function createToken(UserEntity $user): string
    {
        // Make sure the user is valid
        if (!$user->getEntityId() || !$user->getValue('account_id')) {
            throw new InvalidArgumentException('A valid user entity with account_id and entity_id is required');
        }

        $token = [
            'iss' => 'https://app.netric.com',
            'aud' => 'https://app.netric.com',
            'iat' => time(),
            'nbf' => time(),
            'user_id' => $user->getEntityId(),
            'name' => $user->getName(),
            'email' => $user->getValue('email'),
            'account_id' => $user->getValue('account_id'),
        ];

        return JWT::encode($token, $this->privateKey, self::JWT_ALGORITHM);
    }

    /**
     * Get a user id from the header (if present)
     *
     * @return bool true if params set, false if failed
     */
    private function setParamsFromTokens(): bool
    {
        // Check if we have already set params
        if ($this->tokenAccountId && $this->tokenUserId) {
            return true;
        }

        // Get the params from the token
        try {
            $tokenParams = (array) JWT::decode($this->authToken, $this->privateKey, [self::JWT_ALGORITHM]);

            if (empty($tokenParams['user_id']) || empty($tokenParams['account_id'])) {
                $this->lastFailureReason = 'Correct params not set';
                return false;
            }

            $this->tokenUserId = $tokenParams['user_id'];
            $this->tokenAccountId = $tokenParams['account_id'];

            return true;
        } catch (UnexpectedValueException $exception) {
            $this->lastFailureReason = $exception->getMessage();
            return false;
        }

        // Something went wrong, give them nothing
        return null;
    }
}
