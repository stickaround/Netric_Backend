<?php

declare(strict_types=1);

namespace Netric\Account\InitData\Sets;

use Netric\Account\Account;
use Netric\Account\InitData\InitDataInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\ObjectTypes;
use Netric\Entity\ObjType\UserEntity;

/**
 * Initializer to make sure the account root folder exists
 */
class FirstTeamInitData implements InitDataInterface
{
     /**
     * Entity loader
     */
    private Entityloader $entityLoader;

    /**
     * Constructor
     *
     * @param EntityLoader $entityLoader
     */
    public function __construct(EntityLoader $entityLoader)
    {
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
        $team = $this->entityLoader->getByUniqueName(
                ObjectTypes::USER_TEAM,
                $account->getOrgName(),
                $account->getAccountId()
            );
        
         // If it does not already exist, then create it
        if (!$team) {
            $team = $this->entityLoader->create(
                ObjectTypes::USER_TEAM,
                $account->getAccountId()
            );
        }

        $userArray = $account->toArray();
        $team->fromArray($userArray);
        
        $this->entityLoader->save($team, $account->getSystemUser());
        return true;
    }

}
?>