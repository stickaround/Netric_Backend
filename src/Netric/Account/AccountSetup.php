<?php

namespace Netric\Account;

use Netric\Application\DataMapperInterface;
use Netric\Application\Exception\AccountAlreadyExistsException;
use Netric\Application\Exception\CouldNotCreateAccountException;

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
     * Constructor
     *
     * @param DataMapperInterface $appDataMapper
     */
    public function __construct(DataMapperInterface $appDataMapper)
    {
        $this->appDataMapper = $appDataMapper;
    }

    /**
     * Create a new account with a default admin account
     *
     * @param [type] $accountName
     * @param [type] $adminUserName
     * @param [type] $adminUserEmail
     * @param [type] $adminPassword
     * @return void
     */
    public function createAccount(
        string $accountName,
        string $adminUserName,
        string $adminUserEmail,
        string $adminPassword
    ) {
        // Make sure the account does not already exists
        if ($this->appDataMapper->getAccountByName($accountName)) {
            throw new AccountAlreadyExistsException($accountName . " already exists");
        }

        // Make sure the name is clearn
        $cleanedAccountName = $this->getUniqueAccountName($accountName);

        // Add new account record
        $accountId = $this->appDataMapper->createAccount($cleanedAccountName);

        // Make sure the created account is valid
        if (!$accountId) {
            throw new CouldNotCreateAccountException(
                "Failed creating account " . $this->appDataMapper->getLastError()->getMessage()
            );
        }
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
}
