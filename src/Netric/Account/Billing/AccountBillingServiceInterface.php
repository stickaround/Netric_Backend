<?php

declare(strict_types=1);

namespace Netric\Account\Billing;

use Netric\Account\Account;
use Netric\PaymentGateway\PaymentMethod\CreditCard;
use Netric\Entity\EntityInterface;

/**
 * The account billing service is used to manage billing for each netric tennant (account)
 */
interface AccountBillingServiceInterface
{
    /**
     * Loop through all active accounts and bill them if there is anything due bill them
     */
    public function billAllDueAccounts(): void;

    /**
     * If an account is due for billing, bill them
     *
     * If there is some sort of error, like the user does not have billing info
     * or the payment gateway is failing, then this will return false.
     *
     * The return value can be used to set a flag that the account is not in
     * a healthy status.
     *
     * @param Account $account
     * @return bool True if success (either billed or skipped)
     */
    public function billAmountDue(Account $account): bool;

    /**
     * Save a default payment profile for a contact
     *
     * @param string $contactId The contact that owns the payment profile in the mainAccount (not an entity in the current tennant)
     * @param CreditCard $card The credit card that will be using to bill the customer
     * @return string The name of the payment profile
     */
    public function saveDefaultPaymentProfile(string $contactId, CreditCard $card): string;

    /**
     * Get the name of the default payment profile for a contact
     *
     * @param Account $account The account of the current tennant
     * @param string $contactId The contact that owns the payment profile
     * @return string
     */
    public function getDefaultPaymentProfileName(Account $account, string $contactId): string;

    /**
     * Get the contact ID assoicated with the account we are billing
     *
     * The contact belongs to the $this->mainAccountId which can get a bit confusing,
     * but this is because all billing happens under one netric account, for all other
     * accounts so we can utilize the invoicing and billing capabilities of our own system.
     *
     * This may not exist for an account, and if that is the case the billing will fail which
     * means the account admin needs to log in and update billing details - where the contact
     * gets created for the account along with the payment profile.
     *
     * @param Account $account
     * @return EntityInterface
     */
    public function getContactForAccount(Account $account): EntityInterface;

    /**
     * Determine how many active users we have with a simple query
     *
     * @param string $accountId
     * @return int Number of non-system active users
     */
    public function getNumActiveUsers(string $accountId): int;
}
