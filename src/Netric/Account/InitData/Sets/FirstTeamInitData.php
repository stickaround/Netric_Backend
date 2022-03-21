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
                $account->getAccountId());
        }

        $team->setValue("uname", UserEntity::USER_SYSTEM);
        $team->setValue("name", UserEntity::USER_SYSTEM);


        // echo 'Name--->';
        // echo $account->getName();
        // echo '---------';
        // $userArray = $account->getUser();

        // if($userArray){
        //     echo ' Found Username----->';
        //     echo $userArray->getValue("name");
        //     echo ' Full Name----->';
        //     echo $userArray->getValue('full_name');
        // }else{
        //     echo ' Not Found';
        //     $userArray = $account->getSystemUser();
        //     echo ' Username----->';
        //     echo $userArray->getValue("name");
        //     echo ' Full Name----->';
        //     echo $userArray->getValue('full_name');
        // }

        //print_r($userArray);
        // die('I am here');
        //
        //$team->fromArray($userArray);
        //$userArray = $account->toArray();
        //$team->fromArray($userArray);
        //die('I am account');
        // $team->setValue("name", $account->getName());
        // $team->setValue("id", $account->getAccountId());

        // $team->setValue("uname", UserEntity::USER_SYSTEM);
        // $team->setValue("name", UserEntity::USER_SYSTEM);

        // $team->setValue("name", $account->getName());
        // $team->setValue("id", $account->getAccountId());
       // die('I m In');
        
        $this->entityLoader->save($team, $account->getSystemUser());
        return true;
    }

}
?>