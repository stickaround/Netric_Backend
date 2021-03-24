<?php

namespace Netric\Authentication;

use Netric\Authentication\Token\AuthenticationTokenInterface;
use Netric\Authentication\Token\HmakToken;
use Netric\Entity\EntityLoader;
use Netric\EntityQuery\EntityQuery;
use Netric\EntityQuery\Index\IndexInterface;
use Netric\Request\RequestInterface;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Authentication\Token\PrivateKeyToken;
use Netric\Authentication\Token\JsonWebToken;
use Netric\Entity\ObjType\UserEntity;
use Netric\Account\AccountContainerInterface;
use Netric\Entity\EntityLoaderFactory;
use Netric\Authentication\Exception\NotAuthenticatedException;

/**
 * Authenticate an external request
 */
class AuthenticationService
{
    /**
     * Private key for generating hmac
     *
     * @var string
     */
    private $privateKey = null;

    /**
     * Account container loader
     *
     * @var AccountContainerInterface
     */
    private $accountContainer = null;

    /**
     * Update request object
     *
     * @var RequestInterface
     */
    private $request = null;

    /**
     * The number of hash iterations to perform
     *
     * Must be at least 2 and should not be more than 256
     *
     * @const int
     */
    const HASH_ITERATIONS = 4;

    /**
     * Default expiration (in seconds) of new sessions
     *
     * -1 means the session never expires
     *
     * @const int
     */
    const DEFAULT_EXPIRES = -1;

    /**
     * Error codes
     *
     * @const string
     */
    const IDENTITY_NOT_FOUND = 'identityNotFound';
    const IDENTITY_AMBIGUOUS = 'identityAmbiguous';
    const CREDENTIAL_INVALID = 'credentialInvalid';
    const IDENTITY_DISABLED  = 'identityDisabled';
    const ACCOUNT_NOT_FOUND  = 'accountNotFound';
    const UNCATEGORIZED      = 'uncategorized';
    const GENERAL            = 'general';

    /**
     * Last error code
     *
     * @var string
     */
    private $lastErrorMessage = null;

    /**
     * Error Messages
     *
     * @var array
     */
    protected $messageTemplates = [
        self::IDENTITY_NOT_FOUND => 'Invalid identity',
        self::IDENTITY_AMBIGUOUS => 'Identity is ambiguous',
        self::IDENTITY_DISABLED  => 'User is no longer active',
        self::CREDENTIAL_INVALID => 'Invalid password',
        self::ACCOUNT_NOT_FOUND  => 'Account does not exist or could not be loaded',
        self::UNCATEGORIZED      => 'Authentication failed',
        self::GENERAL            => 'Authentication failed',
    ];

    /**
     * Cache the currently authenticated user so we don't re-validate and load every call
     *
     * @var UserEntity
     */
    private $cachedIdentity = null;

    /**
     * Supported token schemas
     */
    const METHOD_PRIVATE_KEY = 'NTRC-PKY';
    const METHOD_JSON_WEB_TOKEN = 'NTRC-JWT';
    const METHOD_HMAC = 'NTRC-HMC';
    const METHOD_DEFAULT = self::METHOD_JSON_WEB_TOKEN;

    /**
     * Class constructor
     *
     * @param string $privateKey A server-side private key for hmac
     * @param IndexInterface $userIndex for querying users by id
     * @param EntityLoader $userLoader Loader to get user entities by id
     * @param RequestInterface $request
     */
    public function __construct(
        string $privateKey,
        AccountContainerInterface $accountContainer,
        RequestInterface $request
    ) {
        $this->privateKey = $privateKey;
        $this->accountContainer = $accountContainer;
        $this->request = $request;
    }

    /**
     * If there is a valid user token, return the identity
     *
     * @return AuthenticationIdentity|null
     */
    public function getIdentity(): ?AuthenticationIdentity
    {
        // Check to see if this user id has already been validated
        if ($this->cachedIdentity) {
            return $this->cachedIdentity;
        }

        // Get the token
        $token = $this->getTokenFromRequest();
        if ($token && $token->tokenIsValid()) {
            $this->cachedIdentity = new AuthenticationIdentity(
                $token->getAccountId(),
                $token->getUserId()
            );
            return $this->cachedIdentity;
        }

        return null;
    }

    /**
     * Get identity and throw an exception if none exists
     *
     * @throws NotAuthenticatedException
     */
    public function getIdentityRequired(): AuthenticationIdentity
    {
        if (!$this->getIdentity()) {
            throw new NotAuthenticatedException("No user has been authenticated yet.");
        }

        return $this->getIdentity();
    }

    /**
     * Set the current identity
     *
     * @param AuthenticationIdentity $identity
     * @return void
     */
    public function setIdentity(AuthenticationIdentity $identity): void
    {
        $this->cachedIdentity = $identity;
    }

    /**
     * Get explanation for why authentication failed
     *
     * @return string
     */
    public function getFailureReason()
    {
        return ($this->lastErrorMessage) ? $this->messageTemplates[$this->lastErrorMessage] : null;
    }

    /**
     * Clear authenticated user cache to force re-authentication
     */
    public function clearAuthorizedCache()
    {
        $this->cachedIdentity = null;
    }

    /**
     * Authenticate a user and return a header session string that can be used
     *
     * @param string $username Unique username
     * @param string $password Clear text password for the selected user
     * @param string $accountName The name of the account the user belongs to
     * @return string on success a session string, null on failure
     */
    public function authenticate(string $username, string $password, string $accountName)
    {
        // Set all initial values and remove validated cache
        $this->clearAuthorizedCache();
        $user = null;

        // Make sure we were given credentials
        if (!$username || !$password || !$accountName) {
            $this->lastErrorMessage = self::IDENTITY_AMBIGUOUS;
            return null;
        }

        // Get the account
        $account = $this->accountContainer->loadByName($accountName);
        if (!$account) {
            $this->lastErrorMessage = self::ACCOUNT_NOT_FOUND;
            return null;
        }

        // Load the user by username
        // TODO: We really should stop using the service manager in classes like this
        $entityLoader = $account->getServiceManager()->get(EntityLoaderFactory::class);
        $user = $entityLoader->getByUniqueName(ObjectTypes::USER, strtolower($username), $account->getAccountId());
        if (!$user) {
            $this->lastErrorMessage = self::IDENTITY_NOT_FOUND;
            return null;
        }

        // Make sure user is active
        if (true !== $user->getValue("active")) {
            $this->lastErrorMessage = self::IDENTITY_DISABLED;
            return null;
        }

        // Get the salt
        $salt = $user->getValue("password_salt");

        // Check that the hashed passwords are the same
        $hashedPass = $this->hashPassword($password, $salt);
        if (md5($hashedPass) != $user->getValue("password")) {
            $this->lastErrorMessage = self::CREDENTIAL_INVALID;
            return null;
        }

        // Cache for future calls to getIdentity because validation can be expensive
        $this->cachedIdentity = new AuthenticationIdentity(
            $account->getAccountId(),
            $user->getEntityId()
        );

        // Create a session to
        return $this->getEncodedToken($user);
    }

    /**
     * Set the private key used for encryption
     *
     * @param string $privateKey
     */
    public function setPrivateKey(string $privateKey)
    {
        $this->privateKey = $privateKey;
    }

    /**
     * Create a session token to be used by a client for authenticated requests
     *
     * @param UserEntity $user
     * @return string
     */
    private function getEncodedToken(UserEntity $user, $method = self::METHOD_DEFAULT): string
    {
        $token = $this->createTokenInstance($method);
        return $method . " " . $token->createToken($user);
    }

    /**
     * Get expires seconds
     *
     * @return int Current timestamp plus number of seconds until it expires
     */
    public function getExpiresTs()
    {
        if (self::DEFAULT_EXPIRES > 0) {
            return time() + self::DEFAULT_EXPIRES;
        }

        return -1;
    }

    /**
     * Hash a password
     */
    public function hashPassword($password, $salt)
    {
        // Iterate hash 4 levels
        $numberOfIterations = self::HASH_ITERATIONS;
        $hpass = md5($salt . $password);

        do {
            $hpass = md5($hpass . $password);
        } while (--$numberOfIterations);

        return $hpass;
    }

    /**
     * Set the local request object
     *
     * This is a required dependency and injected at construction,
     * but it can be swapped out manually for things such as
     * unit testing.
     *
     * @param RequestInterface $request
     */
    public function setRequest(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Get instance of auth token from header or supplied param
     */
    private function getTokenFromRequest(): ?AuthenticationTokenInterface
    {
        // Get authentication from either headers/get/post
        $fullAuthHeader = $this->request->getParam("Authentication");

        // Make sure the auth token exists and is at least 9 chars (length of method and space)
        if (!$fullAuthHeader || strlen($fullAuthHeader) < 9) {
            // TODO: throw an exception?
            return null;
        }

        list($methodName, $token) = explode(" ", $fullAuthHeader);

        // Make token an empty string if it does not exists (not a valid Authentication header)
        if ($token === null) {
            $token = "";
        }

        return $this->createTokenInstance($methodName, $token);
    }

    /**
     * Token factory
     *
     * @param string $tokenMethod one of self::METHOD_*
     * @param string $encodedToken Encoded token from request
     * @return AuthenticationTokenInterface
     */
    private function createTokenInstance(string $tokenMethod, $encodedToken = ""): AuthenticationTokenInterface
    {
        if (self::METHOD_PRIVATE_KEY == $tokenMethod) {
            return new PrivateKeyToken($this->privateKey, $encodedToken);
        }

        if (self::METHOD_HMAC == $tokenMethod) {
            return new HmakToken($this->privateKey, $encodedToken);
        }

        // Default to JWT
        return new JsonWebToken($this->privateKey, $encodedToken);
    }
}
