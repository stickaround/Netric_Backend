<?php

/**
 * Email Account entity extension
 *
 * @author Marl Tumulak <marl.tumulak@aereus.com>
 * @copyright 2016 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\ServiceManager\ServiceLocatorInterface;
use Netric\Crypt\BlockCipher;
use Netric\Crypt\VaultServiceFactory;
use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\EntityQuery\EntityQuery;
use Netric\Entity\ObjType\UserEntity;
use Netric\EntityDefinition\ObjectTypes;
use Netric\EntityQuery\Index\IndexFactory;

/**
 * Activty entity used for logging activity logs
 */
class EmailAccountEntity extends Entity implements EntityInterface
{
    const TYPE_DROPBOX = 'dropbox';
    const TYPE_REPLY = 'none';
    const TYPE_IMAP = 'imap';
    const TYPE_POP3 = 'pop3';

    /**
     * Callback function used for derrived subclasses
     *
     * @param ServiceLocatorInterface $serviceLocator ServiceLocator for injecting dependencies
     * @param UserEntity $user The user that is acting on this entity
     */
    public function onBeforeSave(ServiceLocatorInterface $serviceLocator, UserEntity $user)
    {
        // If the password was updated for this user then encrypt it
        if ($this->fieldValueChanged("password")) {
            $vaultService = $serviceLocator->get(VaultServiceFactory::class);
            $blockCipher = new BlockCipher($vaultService->getSecret("EntityEnc"));
            $this->setValue("password", $blockCipher->encrypt($this->getValue("password")));
        }

        // If address is changed, then we need to make sure that there is no duplicate address
        if ($this->fieldValueChanged("address")) {
            $query = new EntityQuery(ObjectTypes::EMAIL_ACCOUNT, $user->getAccountId(), $user->getEntityId());
            $query->where('address')->equals($this->getValue("address"));
            $query->where('entity_id')->doesNotEqual($this->getEntityId());

            $index = $serviceLocator->get(IndexFactory::class);
            $res = $index->executeQuery($query);

            // If duplicate is found, throw an exception
            if ($res->getTotalNum() >= 1) {
                throw new \RuntimeException("Email account address already exists.");
            }
        }
    }
}
