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
class UsersInitData implements InitDataInterface
{
    /**
     * List of users to create
     */
    private array $usersData = [];

    /**
     * Entity loader
     */
    private Entityloader $entityLoader;

    /**
     * Constructor
     *
     * @param array $usersData
     * @param EntityLoader $entityLoader
     */
    public function __construct(array $usersData, EntityLoader $entityLoader)
    {
        $this->usersData = $usersData;
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
        foreach ($this->usersData as $userData) {
            if (!$this->entityLoader->getByUniqueName(ObjectTypes::USER, $userData['name'], $account->getAccountId())) {
                $user = $this->entityLoader->create(ObjectTypes::USER, $account->getAccountId());
                $user->fromArray($userData);
                $this->entityLoader->save($user, $account->getSystemUser());
            }
        }

        return true;
    }
}
