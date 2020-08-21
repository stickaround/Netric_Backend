<?php

namespace Netric\Account;

use Netric\Application\Application;
use Netric\Authentication\AuthenticationIdentity;
use Netric\ServiceManager\AccountServiceManagerInterface;
use Netric\ServiceManager\AccountServiceManager;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityQuery\EntityQuery;
use Netric\Entity\EntityLoaderFactory;
use Netric\Authentication\AuthenticationServiceFactory;
use Netric\EntityQuery\Index\IndexFactory;
use Netric\Config\ConfigFactory;
use Netric\EntityDefinition\ObjectTypes;
use DateTime;

/**
 * Netric account instance
 */
class Account
{
    /**
     * Unique account ID
     *
     * @var string
     */
    private $id = '';

    /**
     * Unique account name
     *
     * @var string
     */
    private $name = "";

    /**
     * The name of the database
     *
     * @var string
     */
    private $dbname = "netric";

    /**
     * Instance of netric application
     *
     * @var Application
     */
    private $application = null;

    /**
     * Service manager for this account
     *
     * @var AccountServiceManagerInterface
     */
    private $serviceManager = null;

    /**
     * Optional description
     *
     * @var string
     */
    private $description = "";

    /**
     * Property to set the current user rather than using the auth service
     *
     * @var UserEntity
     */
    public $currentUserOverride = null;

    /**
     * The status of this account
     *
     * @var int
     */
    private $status = null;
    const STATUS_ACTIVE = 1;
    const STATUS_EXPIRED = 2;
    const STATUS_DELETED = 3;

    /**
     * The last time this account was successfully billed
     */
    private ?DateTime $billingLastBilled = null;

    /**
     * Flag used to push the admin user to update billing profile
     */
    private bool $billingForceUpdate = false;

    /**
     * Main account contact id - the id of the contact/customer for billing
     *
     * This contact is stored in the global settings mainAccountId's netric
     * account for billing, marketing, and support (tickets).
     */
    private string $mainAccountContactId = '';

    /**
     * Initialize netric account
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->application = $app;

        $this->serviceManager = new AccountServiceManager($this);

        // Set default status
        $this->status = self::STATUS_ACTIVE;
    }

    /**
     * Load application data from an associative array
     *
     * @param array $data
     * @return bool true on successful load, false on failure
     */
    public function fromArray($data)
    {
        // Check required fields
        if (!$data['account_id'] || !$data['name']) {
            return false;
        }

        $this->id = $data['account_id'];
        $this->name = $data['name'];

        if (isset($data['database']) && $data['database']) {
            $this->dbname = $data['database'];
        }

        if (isset($data['description']) && $data['description']) {
            $this->description = $data['description'];
        }

        if (isset($data['billing_last_billed'])) {
            $this->billingLastBilled = new DateTime($data['billing_last_billed']);
        }

        if (isset($data['billing_force_update'])) {
            $this->billingForceUpdate = $data['billing_force_update'];
        }

        if (isset($data['main_account_contact_id'])) {
            $this->mainAccountContactId = $data['main_account_contact_id'];
        }

        return true;
    }

    /**
     * Export internal properties to an associative array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            "account_id" => $this->id,
            "name" => $this->name,
            "database" => $this->dbname,
            "description" => $this->description,
            "billing_force_update" => $this->billingForceUpdate,
            'main_account_contact_id' => $this->mainAccountContactId
        ];
    }

    /**
     * Get account id
     *
     * @return string
     */
    public function getAccountId(): string
    {
        return $this->id;
    }

    /**
     * Get account unique name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the optional description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get ServiceManager for this account
     *
     * @return AccountServiceManagerInterface
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }

    /**
     * Get application object
     *
     * @return Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Get the main account contact id for billing, support, and marketing
     *
     * This is the ID of the contact in the main acccount (not the current account)
     * where all billing and support is managed. This is most likely the parent company
     * account (aereus) in production.
     *
     * @return string
     */
    public function getMainAccountContactId(): string
    {
        return $this->mainAccountContactId;
    }

    /**
     * @deprecated We now use the AuthenticationService
     *
     * Override the currently authenticated user with a specific user
     *
     * This is often used in testing and in background services where
     * there is no current authenticated user but we need to setup one
     * manually for act on behalf of a user.
     *
     * @param UserEntity $user
     */
    public function setCurrentUser(UserEntity $user)
    {
        $this->currentUserOverride = $user;

        // Clear the service locator since user is often injected as a dependency
        $this->getServiceManager()->clearLoadedServices();

        $identity = new AuthenticationIdentity($this->getAccountId(), $user->getEntityId());
        $this->getServiceManager()->get(AuthenticationServiceFactory::class)->setIdentity($identity);
    }

    /**
     * Get the system user which can be used for 'god' rights when needed
     *
     * @return UserEntity
     */
    public function getSystemUser(): UserEntity
    {
        $entityLoader = $this->getServiceManager()->get(EntityLoaderFactory::class);
        $systemUser = $entityLoader->create(ObjectTypes::USER, $this->getAccountId());
        $systemUser->setValue("uname", UserEntity::USER_SYSTEM);
        $systemUser->setValue("name", UserEntity::USER_SYSTEM);
        return $systemUser;
    }

    /**
     * Get the anonymous user which can be used for unauthenticated operations
     *
     * @return UserEntity
     */
    public function getAnonymousUser(): UserEntity
    {
        $entityLoader = $this->getServiceManager()->get(EntityLoaderFactory::class);
        $anonymousUser = $entityLoader->create(ObjectTypes::USER, $this->getAccountId());
        $anonymousUser->setValue("uname", UserEntity::USER_ANONYMOUS);
        $anonymousUser->setValue("name", UserEntity::USER_ANONYMOUS);
        return $anonymousUser;
    }

    /**
     * Get the currently authenticated user from the authentication service
     *
     * @return UserEntity
     */
    public function getAuthenticatedUser(): UserEntity
    {
        // Entity loader will be needed once we have determined a user id to load
        $entityLoader = $this->getServiceManager()->get(EntityLoaderFactory::class);

        // Get the authentication service
        $auth = $this->getServiceManager()->get(AuthenticationServiceFactory::class);

        // Check if the current session is authenticated
        if ($auth->getIdentity()) {
            return $entityLoader->getEntityById(
                $auth->getIdentity()->getUserId(),
                $this->getAccountId()
            );
        }

        // Return anonymous user since we could not find the authenticated user
        return $this->getAnonymousUser();
    }

    /**
     * Get user by id or name
     *
     * If neither id or username are defined, then try to get the currently authenticated user.
     * If no users are authenticated, then this function will return false.
     *
     * @param string $userId The userId of the user to get
     * @param string $username Get user by name
     * @return UserEntity|bool user on success, false on failure
     */
    public function getUser($userId = null, $username = null)
    {
        // Check to see if we have manually set the current user and if so skip session auth
        if ($this->currentUserOverride) {
            return $this->currentUserOverride;
        }

        // Entity loader will be needed once we have determined a user id to load
        $entityLoader = $this->getServiceManager()->get(EntityLoaderFactory::class);

        // Try to get the currently logged in user from theauthentication service if not provided
        if (!$userId && !$username) {
            // Get the authentication service
            $auth = $this->getServiceManager()->get(AuthenticationServiceFactory::class);

            // Check if the current session is authenticated
            if ($auth->getIdentity()) {
                return $entityLoader->getEntityById($auth->getIdentity()->getUserId(), $this->getAccountId());
            }
        }

        /*
         * Load the user with the loader service.
         * This makes it unnecessary to cache the current user locally
         * since the loader handles making sure there is only one instance
         * of each user object in memory.
         */
        if ($userId) {
            return $entityLoader->getEntityById($userId, $this->getAccountId());
        } elseif ($username) {
            $query = new EntityQuery(ObjectTypes::USER, $this->getAccountId());
            $query->where('name')->equals($username);
            $index = $this->getServiceManager()->get(IndexFactory::class);
            $res = $index->executeQuery($query);
            if ($res->getTotalNum()) {
                return $res->getEntity(0);
            }

            return null;
        }

        // Return anonymous user since we could not find the authenticated user
        return $this->getAnonymousUser();
    }

    /**
     * Set account and username for a user's email address and username
     *
     * @param string $username The user name - unique to the account
     * @param string $emailAddress The email address to pull from
     * @return bool true on success, false on failure
     */
    public function setAccountUserEmail($username, $emailAddress)
    {
        return $this->application->setAccountUserEmail($this->getAccountId(), $username, $emailAddress);
    }

    /**
     * Get the url for this account
     *
     * @param bool $includeProtocol If true prepend the default protocol
     * @return string A url like https://aereus.netric.com
     */
    public function getAccountUrl($includeProtocol = true)
    {
        // Get application config
        $config = $this->getServiceManager()->get(ConfigFactory::class);

        // Initialize return value
        $url = "";

        // Prepend protocol
        if ($includeProtocol) {
            $url .= ($config->use_https) ? "https://" : "http://";
        }

        // Add account third level
        $url .= $this->name . ".";

        // Add the rest of the domain name
        $url .= $config->localhost_root;

        return $url;
    }
}
