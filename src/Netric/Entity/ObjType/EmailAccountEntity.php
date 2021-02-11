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
use Netric\Entity\ObjType\UserEntity;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\EntityDefinition;

/**
 * Activty entity used for logging activity logs
 */
class EmailAccountEntity extends Entity implements EntityInterface
{
    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader)
    {
        parent::__construct($def, $entityLoader);
    }

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
    }
}
