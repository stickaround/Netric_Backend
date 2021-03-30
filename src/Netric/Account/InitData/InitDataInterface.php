<?php

declare(strict_types=1);

namespace Netric\Account\InitData;

use Netric\Account\Account;

/**
 * Interface for initial data importers/updaters
 */
interface InitDataInterface
{
    /**
     * Insert or update initial data for account
     *
     * @param Account $account
     * @return bool
     */
    public function setInitialData(Account $account): bool;
}
