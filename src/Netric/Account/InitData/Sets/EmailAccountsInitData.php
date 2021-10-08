<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Account\InitData\InitDataInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\ObjectTypes;

/**
 * Initializer to make sure accounts have a default set of groupings
 */
class EmailAccountsInitData implements InitDataInterface
{
    /**
     * List of worfklows to create
     */
    private array $emailAccountsData = [];

    /**
     * Entity loader
     */
    private Entityloader $entityLoader;

    /**
     * Constructor
     */
    public function __construct(
        array $emailAccountsData,
        EntityLoader $entityLoader,
    ) {
        $this->emailAccountsData = $emailAccountsData;
        $this->entityLoader = $entityLoader;
    }

    /**
     * Insert or update initial data for account
     *
     * @param Account $account
     * @return bool
     */
    public function setInitialData(Account $account): bool
    {
        foreach ($this->emailAccountsData as $emailAcocuntData) {
            // Get the existing account by uname
            $emailAccount = $this->entityLoader->getByUniqueName(
                ObjectTypes::EMAIL_ACCOUNT,
                $emailAcocuntData['uname'],
                $account->getAccountId()
            );

            // If it does not already exist, then create it
            if (!$emailAccount) {
                $emailAccount = $this->entityLoader->create(
                    ObjectTypes::EMAIL_ACCOUNT,
                    $account->getAccountId()
                );
            }

            // Set fields from data array and save
            // second param will only update provided fields so we don't
            // overwrite entity_id and such
            $emailAccount->fromArray($emailAcocuntData, true);
            $this->entityLoader->save(
                $emailAccount,
                $account->getSystemUser()
            );
        }

        return true;
    }
}
