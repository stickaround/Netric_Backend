<?php

namespace Netric\Account;

use Netric\Application\DataMapperInterface;
use Netric\Application\Exception\AccountAlreadyExistsException;
use Netric\Application\Exception\CouldNotCreateAccountException;
use Netric\Account\Account\InitData\InitDataInterface;
use Netric\EntityDefinition\ObjectTypes;
use rtf;

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
    private AccountContainer $accountContianer;

    /**
     * Entityloader user to create users
     *
     * @var EntityLoader
     */
    private EntityLoader $entityLoader;

    /**
     * Constructor
     *
     * @param DataMapperInterface $appDataMapper
     * @param AccountContainer $accountContainer
     * @param InitDataInterface[] $dataImporters
     */
    public function __construct(DataMapperInterface $appDataMapper, AccountContainer $accountContainer, array $dataImporters)
    {
        $this->appDataMapper = $appDataMapper;
        $this->accountContianer = $accountContainer;
        $this->dataImporters = $dataImporters;
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
        // Make sure the account does not already exists
        if ($this->appDataMapper->getAccountByName($accountName)) {
            throw new AccountAlreadyExistsException($accountName . " already exists");
        }

        // TODO: Make sure the name is valid
        //$cleanedAccountName = $this->getUniqueAccountName($accountName);

        // Add new account record
        $accountId = $this->appDataMapper->createAccount($cleanedAccountName);

        // Make sure the created account is valid
        if (!$accountId) {
            throw new CouldNotCreateAccountException(
                "Failed creating account " . $this->appDataMapper->getLastError()->getMessage()
            );
        }

        // Load the newly created account
        $account = $this->accountContianer->loadById($accountId);

        // Make sure it worked
        if ($account === null) {
            throw new CouldNotCreateAccountException('Account creation failed');
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

        $cleanedName = strtolower($originalName);
        $cleanedName = preg_replace("/[^a-z0-9]/", '', $cleanedName);

        // Check if the name is unique
        if ($this->appDataMapper->getAccountByName($cleanedName)) {
            // Name is already taken, append a number
            // TODO: this is not very performant but simple
            $accounts = $this->appDataMapper->getAccounts();
            $numAccountsWithName = 0;
            foreach ($accounts as $accountData) {
                // Skip over if account name is too short
                if (strlen($accountData['name']) < strlen($cleanedName)) {
                    continue;
                }

                // Increment counter if the name is similar
                $beginningOfAccName = substr($accountData['name'], 0, strlen($cleanedName));
                if ($beginningOfAccName === $cleanedName) {
                    $numAccountsWithName++;
                }
            }

            // Append next number
            $cleanedName .= ++$numAccountsWithName;
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
}
