<?php

namespace Netric\Account;

use DateInterval;
use Netric\Application\Application;
use Netric\Authentication\AuthenticationIdentity;
use Netric\ServiceManager\ApplicationServiceManager;
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
     * Organization or company title
     *
     * @var string
     */
    private $orgName = "";

    /**
     * Instance of netric application
     *
     * @var Application
     */
    private $application = null;

    /**
     * Service manager for this account
     *
     * @var ApplicationServiceManager
     */
    private $serviceManager = null;

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

    // Account is active and in good billing standing
    const STATUS_ACTIVE = 1;
    // Used historically when a free trial expires
    const STATUS_EXPIRED = 2;
    // Will be used for archiving
    const STATUS_DELETED = 3;
    // Billing issues
    const STATUS_PASTDUE = 4;

    /**
     * The last time this account was successfully billed
     */
    private ?DateTime $billingLastBilled = null;

    /**
     * THe next time we should try to bill this account
     */
    private ?DateTime $billingNextBill = null;

    /**
     * yearly yearly (or bi-yearly) with this param.
     *
     * @var int
     */
    private int $billingMonthInterval = 1;

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

        $this->serviceManager = $app->getServiceManager();

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
        $this->orgName =  (isset($data['org_name'])) ? $data['org_name'] : $data['name'];

        if (isset($data['status']) && $data['status']) {
            $this->dbname = $data['status'];
        }

        if (isset($data['billing_last_billed'])) {
            $this->billingLastBilled = new DateTime($data['billing_last_billed']);
        }

        if (isset($data['billing_next_bill'])) {
            $this->billingNextBill = new DateTime($data['billing_next_bill']);
        }

        if (isset($data['billing_month_interval'])) {
            $this->billingMonthInterval = $data['billing_month_interval'];
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
        // Format DateTime objects to strings
        $billingLastBilled =
            $this->billingLastBilled ?
            $this->billingLastBilled->format("Y-m-d") :
            '';

        $billingNextBill =
            $this->billingNextBill ?
            $this->billingNextBill->format("Y-m-d") :
            '';

        return [
            "account_id" => $this->id,
            "name" => $this->name,
            "org_name" => $this->orgName,
            "status" => $this->status,
            "status_name" => $this->getStatusName(),
            'main_account_contact_id' => $this->mainAccountContactId,
            'billing_last_billed' => $billingLastBilled,
            'billing_next_bill' => $billingNextBill,
            'billing_month_interval' => $this->billingMonthInterval,
        ];
    }

    /**
     * If we have billed for this account, get the last billed date
     *
     * @return DateTime|null Null if we never billed
     */
    public function getBillingLastBilled(): ?DateTime
    {
        return $this->billingLastBilled;
    }

    /**
     * Update when we last billed this account
     *
     * @param DateTime $time
     * @return void
     */
    public function setBillingLastBilled(DateTime $time): void
    {
        $this->billingLastBilled = $time;
    }

    /**
     * Get the next time we should be billing this account
     *
     * @return DateTime|null Null if we never billed
     */
    public function getBillingNextBill(): ?DateTime
    {
        return $this->billingNextBill;
    }

    /**
     * Set when we should be billing this account next
     *
     * @param DateTime $nextBillDate
     * @return void
     */
    public function setBillingNextBill(DateTime $nextBillDate): void
    {
        $this->billingNextBill = $nextBillDate;
    }

    /**
     * Interval (in months) between billing cycles
     *
     * @return int
     */
    public function getBillingMonthInterval(): int
    {
        return $this->billingMonthInterval;
    }

    /**
     * Set the interval - in months - between billing cycles
     *
     * @param int $interval
     * @return void
     */
    public function setBillingMonthInterval(int $interval): void
    {
        $this->billingMonthInterval = $interval;
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
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the company/organization name
     *
     * @return string
     */
    public function getOrgName(): string
    {
        return $this->orgName;
    }

    /**
     * Get ServiceManager for this account
     *
     * @return ApplicationServiceManager
     */
    public function getServiceManager(): ApplicationServiceManager
    {
        return $this->serviceManager;
    }

    /**
     * Get application object
     *
     * @return Application
     */
    public function getApplication(): Application
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
     * Set the contact ID for this account in the main account
     *
     * The main account is the netric account that all other accounts
     * will be managed, supported, and billed under. This main account
     * will also have a contact associated with each craeted account to
     * manage support and invoicing.
     *
     * @param string $mainAccountContactId
     * @return void
     */
    public function setMainAccountContactId(string $mainAccountContactId): void
    {
        $this->mainAccountContactId = $mainAccountContactId;
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

        // First check to see if the system user exists
        $systemUser = $entityLoader->getByUniqueName(
            ObjectTypes::USER,
            UserEntity::USER_SYSTEM,
            $this->getAccountId()
        );

        if ($systemUser) {
            return $systemUser;
        }

        // If the system user does not yet exist, we are probably calling this function to create the first users
        // and we can safely return a system user without an ID. This could cause downstream issues so we might
        // want to actually create the system user here if it does not exist?
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
    public function getAccountUrl($includeProtocol = true): string
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

    /**
     * Get the status of this account
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get the status name
     */
    public function getStatusName(): string
    {
        switch ($this->status) {
            case self::STATUS_ACTIVE:
                return "Active";
                break;
            case self::STATUS_EXPIRED:
                return "Expired";
                break;
            case self::STATUS_DELETED:
                return "Deleted";
                break;
            case self::STATUS_PASTDUE:
                return "Past Due";
                break;
            default:
                return "Unknown";
        }
    }

    /**
     * Set the next bill date
     *
     * @return DateTime true if the date is updated, false if not updated
     */
    public function calculateAndUpdateNextBillDate(): DateTime
    {
        // Set next billing date based on last billed date (or now if not yet billed)
        $nextBillDate = $this->billingLastBilled ?
            clone $this->billingLastBilled : new DateTime();

        // Now add <interval> months
        $nextBillDate->add(new DateInterval('P' . $this->billingMonthInterval . 'M'));

        // Update
        $this->setBillingNextBill($nextBillDate);
        return $nextBillDate;
    }
}
