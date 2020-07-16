<?php

namespace Netric\Account;

use Netric\Application\Exception\AccountAlreadyExistsException;
use Netric\Application\Exception\CouldNotCreateAccountException;

class AccountManager
{
    /**
     * Initialize a brand new account and create the admin user
     *
     * @param string $accountName A unique name for the new account
     * @param string $adminUserName Required username for the admin/first user
     * @param string $adminUserPassword Required password for the admin
     * @return Account
     */
    public function createAccount($accountName, $adminUserName, $adminUserPassword)
    {
        // Make sure the account does not already exists
        if ($this->accountsIdentityMapper->loadByName($accountName, $this)) {
            throw new AccountAlreadyExistsException($accountName . " already exists");
        }

        // TODO: Check the account name is valid

        // Create new account
        $accountId = $this->accountsIdentityMapper->createAccount($accountName);

        // Make sure the created account is valid
        if (!$accountId) {
            throw new CouldNotCreateAccountException(
                "Failed creating account " . $this->accountsIdentityMapper->getLastError()->getMessage()
            );
        }

        // Load the newly created account
        $account = $this->accountsIdentityMapper->loadById($accountId, $this);

        // Initialize with setup
        $setup = new Setup();
        $setup->setupAccount($account, $adminUserName, $adminUserPassword);

        // If the username is an email address then set the email address to be the username
        if (strpos($adminUserName, '@') !== false) {
            $this->setAccountUserEmail($accountId, $adminUserName, $adminUserName);
        }

        // Return the new account
        return $account;
    }

    /**
     * Delete an account by name
     *
     * @param string $accountName The unique name of the account to delete
     * @return bool on success, false on failure
     */
    public function deleteAccount($accountName)
    {
        // Get account by name
        $account = $this->getAccount(null, $accountName);

        // Delete the account if it is valid
        if ($account->getAccountId()) {
            return $this->accountsIdentityMapper->deleteAccount($account);
        }

        return false;
    }
}
