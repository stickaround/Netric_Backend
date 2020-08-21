<?php

declare(strict_types=1);

namespace Netric\Account\Billing;

use Netric\Account\Account;

/**
 * The account billing service is used to manage billing for each netric tennant (account)
 */
interface AccountBillingServiceInterface
{
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
}
