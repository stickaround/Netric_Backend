<?php

namespace Netric\Authentication\Token;

use Netric\Entity\ObjType\UserEntity;
use InvalidArgumentException;
use UnexpectedValueException;

/**
 * New token using HMAK
 */
class HmakToken implements AuthenticationTokenInterface
{

    /**
     * Indexes of important fields in an client-side session string
     *
     * @const int
     */
    const SESSIONPART_USERID = 0;
    const SESSIONPART_EXPIRES = 1;
    const SESSIONPART_PASSWORD = 2;
    const SESSIONPART_SIGNATURE = 3;
    const SESSIONPART_ACCOUNTID = 4;

    /**
     * The number of params expected in each auth string
     *
     * @const int
     */
    const NUM_AUTH_PARAMS = 4;

    /**
     * Cache the currently authenticated user id so we don't re-validate every request
     *
     * @var int
     */
    private $validatedIdentityUid = null;

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
    public function getUserId(): string
    {
    }

    /**
     * Get the account ID for this user
     *
     * @return string
     */
    public function getAccountId(): string
    {
    }

    /**
     * Generate a token that can be used to verify the authenticity of a request
     *
     * @param UserEntity $user
     * @return string
     */
    public function createToken(UserEntity $user): string
    {
        $hashedPass = $this->hashPassword($password, $salt);

        // Put together basic data
        $sessionData = $this->packSessionString($uid, $expires, $hashedPass);

        // Sign
        $signature = $this->getHmacSignature($sessionData);

        return  $sessionData . ":" . $signature;
    }

    /**
     * Retrieve the authentication header or cookie value
     *
     * @return string
     */
    private function getSessionData()
    {
        // Get authentication from either headers/get/post
        $authStr = $this->request->getParam("Authentication");

        // Extract the parts
        $authData = explode(":", $authStr);

        // Make sure all the required data is in place and no more
        if (self::NUM_AUTH_PARAMS != count($authData)) {
            return null;
        }

        // Appears to have a valid number of params
        return $authData;
    }

    /**
     * Pack session data into a session string
     *
     * @param string $uid The unique id of the authenticated user
     * @param string $expires A timestamp or -1 for no expiration
     * @param string $password A pre-hashed encoded password (needs to be hashed once more)
     * @return string Authorize string
     */
    public function packSessionString($uid, $expires, $password)
    {
        $sessionDataArr = [
            self::SESSIONPART_USERID => $uid,
            self::SESSIONPART_EXPIRES => $expires,
            self::SESSIONPART_PASSWORD => $password,
        ];
        return implode(":", $sessionDataArr);
    }

    /**
     * Generate signature from a normalized string
     *
     * @param string $data The data string to sign
     * @return string A hashed signature
     */
    private function getHmacSignature($data)
    {
        return hash_hmac("sha256", $data, $this->privateKey);
    }

    /**
     * Validate that auth data retrieved from a client session is valid
     *
     * @param string $uid The unique id of the authenticated user
     * @param string $expires A timestamp or -1 for no expiration
     * @param string $password A pre-hashed encoded password (needs to be hashed once more)
     * @param string $signature HMAC signature of the previous params
     * @return bool true if the session is valid, false if invalid or expired
     */
    private function sessionSignatureIsValid($uid, $expires, $password, $signature)
    {
        // Make sure session data is valid via HMAC
        $challengeSignature = $this->getHmacSignature($this->packSessionString($uid, $expires, $password));

        // Check to see if the request has been changed since we last signed it
        if ($challengeSignature != $signature) {
            return false;
        }

        /*
         * TODO: Make sure the user's password has not changed?
         *
         * This Would definitely add to the security, but it also requires a user load
         * every single request from cache. This may not be a problem however because
         * it can be assumed if we are checking authenticated state that we will shortly
         * be loading the user. If we always use the \Netric\EntityLoader then it will
         * always load only once.
         */

        return true;
    }
}
