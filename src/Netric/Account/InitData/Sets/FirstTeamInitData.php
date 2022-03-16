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

        // Set fields from data array and save
        // second param will only update provided fields so we don't
        // overwrite entity_id and such
        // $team->setValue("type_id", 2); // 2 = organization
        // $team->setValue("company", $account->getOrgName());
        // $team->setValue("name", $account->getName());
       
        //$team->fromArray($account->toArray());

        // echo 'Account Id-----';
        // print_r($account->getSystemUser());
        // die('First Team'); 
        // echo 'Account Name---->';
        // echo $account->getName();
        $team->fromArray($account->toArray());
        // print_r($team);
        // die('I m account');
        //$this->entityLoader->save($team, $account->getAccountId());
        $this->entityLoader->save($team, $account->getSystemUser());
        return true;
    }

}
?>