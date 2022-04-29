<?php

namespace Netric\Account;

use Netric\Application\DataMapperInterface;
use Netric\Application\Exception\CouldNotCreateAccountException;
use Netric\Account\Account\InitData\InitDataInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Netric account setup functions
 */
class AccountSetup
{
    /**
     * Application datamapper
     *
     * @var DataMapperInterface
     */
    private $appDataMapper = null;

    /**
     * Array of data importers used to set and update initial data
     *
     * @var InitDataInterface[]
     */
    private array $dataImporters = [];

    /**
     * Account container is used to load accounts
     *
     * @var AccountContainer
     */
    private AccountContainer $accountContainer;

    /**
     * Entityloader user to create users
     *
     * @var EntityLoader
     */
    private EntityLoader $entityLoader;

    /**
     * Names that cannot be used for accounts
     */
    private array $reservedAccountNames;

    /**
     * The ID of the main account that all billing will be under
     *
     * @var string
     */
    private string $mainAccountId;

    /**
     * Constructor
     *
     * @param DataMapperInterface $appDataMapper
     * @param AccountContainer $accountContainer
     * @param InitDataInterface[] $dataImporters
     */
    public function __construct(
        DataMapperInterface $appDataMapper,
        AccountContainer $accountContainer,
        array $dataImporters,
        EntityLoader $entityLoader,
        string $mainAccountId
    ) {
        $this->appDataMapper = $appDataMapper;
        $this->accountContainer = $accountContainer;
        $this->dataImporters = $dataImporters;
        $this->entityLoader = $entityLoader;
        $this->mainAccountId = $mainAccountId;

        // In the future we may want to allow third level domains
        // so we should reserve any names we use as domains now
        $this->reservedAccountNames = [
            "login",
            "aereus",
            "app",
            "api",
            "files",
            "media",
            "public",
        ];
    }

    /**
     * Create a new account with a default admin account
     *
     * @param string $accountName A unique name for the new account
     * @param string $adminUserName Required username for the admin/first user
     * @param string $adminUserPassword Required password for the admin
     * @return Account
     */
    public function createAndInitailizeNewAccount(
        string $accountName,
        string $adminUserName,
        string $adminEmail,
        string $adminPassword
    ): Account {
        // Make sure the name is valid
        $cleanedAccountName = $this->getUniqueAccountName($accountName);

        // Add new account record with the cleaned (url friendly) account name, and the
        // uncleaned version for the organzization name - "mycompany" and "My Company" respectively
        $accountId = $this->accountContainer->createAccount($cleanedAccountName, $accountName);

        // Make sure the created account is valid
        if (!$accountId) {
            throw new CouldNotCreateAccountException(
                "Failed creating account " . $this->accountContainer->getLastError()->getMessage()
            );
        }

        // Load the newly created account
        $account = $this->accountContainer->loadById($accountId);

        // Make sure it worked
        if ($account === null) {
            throw new CouldNotCreateAccountException('Account creation failed');
        }

        // Now create the netric contact that will be used for billing and support
        $mainAccountContactId = $this->createMainAccountContact(
            $accountName,
            $cleanedAccountName,
            $adminEmail
        );
        if ($mainAccountContactId) {
            $this->accountContainer->updateAccount(
                $account->getAccountId(),
                ['main_account_contact_id' => $mainAccountContactId]
            );
        }

        // Set data for this account
        $this->updateDataForAccount($account);

        // Create the admin user
        $adminUser = $this->entityLoader->create(ObjectTypes::USER, $account->getAccountId());
        $adminUser->setValue("name", $adminUserName);
        $adminUser->setValue("email", $adminEmail);
        $adminUser->setValue("password", $adminPassword);
        $adminUser->setIsAdmin(true);
        $this->entityLoader->save($adminUser, $account->getSystemUser());

        return $account;
    }

    /**
     * Create a new contact entity under the main account to handle billing and support
     *
     * We do this because we use netric to bill for netric. In production the main
     * account is the aereus account. In test, the main account is added manually
     * as test data.
     *
     * @param string $companyName
     * @param string $accountName
     * @return string Entity ID of the contact created in the main billing account
     */
    private function createMainAccountContact(
        string $companyName,
        string $accountName,
        string $contactEmail
    ): string {
        $newContact = $this->entityLoader->create(
            ObjectTypes::CONTACT,
            $this->mainAccountId
        );
        $newContact->setValue("type_id", 2); // 2 = organization
        $newContact->setValue("company", $companyName);
        $newContact->setValue("email", $contactEmail);
        $newContact->setValue("netric_account_name", $accountName);

        // Note: our (aereus) account does a bunch of additional work
        // via automated workflows once this entity is created.

        // Save the contact to the main/billing account
        $mainAccount = $this->accountContainer->loadById($this->mainAccountId);
        return $this->entityLoader->save($newContact, $mainAccount->getSystemUser());
    }

    /**
     * Generate a unique account name from a full name like "My Company"
     *
     * @param string $originalName
     * @return string The unique name that can be used for this account
     */
    public function getUniqueAccountName(string $originalName): string
    {
        // If no orignalName was passed, make a new completely unique one
        if (strlen($originalName) === 0) {
            return uniqid('acc');
        }

        // Convert to lower case and remove anythign that won't work for a third-level domain
        $cleanedName = strtolower($originalName);
        $cleanedName = preg_replace("/[^a-z0-9_]/", '', $cleanedName);

        // Check if the name is unique
        if ($this->appDataMapper->getAccountByName($cleanedName) || in_array($cleanedName, $this->reservedAccountNames)) {
            $cleanedName = uniqid($cleanedName);
        }

        return $cleanedName;
    }

    /**
     * Iterate through all accounts and update the default data
     *
     * @return int
     */
    public function updateDataForAllAccounts(): int
    {
        $numUpdated = 0;

        $accounts = $this->appDataMapper->getAccounts();
        foreach ($accounts as $account) {
            if ($this->updateDataForAccount($account)) {
                $numUpdated++;
            }
        }

        return $numUpdated;
    }

    /**
     * Update the default data for a given account
     *
     * @param Account $account
     * @return bool
     */
    public function updateDataForAccount(Account $account): bool
    {
        foreach ($this->dataImporters as $importer) {
            // If one fails, then stop becauase they have downstream dependenceis
            if (!$importer->setInitialData($account)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Delete an account by name
     *
     * @param string $name
     * @return bool
     */
    public function deleteAccountByName(string $name): bool
    {
        $account = $this->accountContainer->loadByName($name);

        if ($account && $account->getAccountId()) {
            return $this->accountContainer->deleteAccount($account);
        }

        // No account found to delete
        return false;
    }
}
