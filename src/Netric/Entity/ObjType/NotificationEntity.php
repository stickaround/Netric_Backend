<?php

/**
 * @author Sky Stebnicki <sky.stebnicki@aereus.com>
 * @copyright 2015 Aereus
 */

namespace Netric\Entity\ObjType;

use Netric\Entity\Entity;
use Netric\Entity\EntityInterface;
use Netric\Entity\EntityLoader;
use Netric\EntityDefinition\EntityDefinition;
use Netric\Account\AccountContainerInterface;
use NotificationPusherSdk\NotificationPusherClientInterface;

/**
 * Notification entity
 */
class NotificationEntity extends Entity implements EntityInterface
{
    /**
     * Notification pusher
     */
    private ?NotificationPusherClientInterface $notificationPusher = null;

    /**
     * The loader for a specific entity
     *
     * @var EntityLoader
     */
    private $entityLoader = null;

    /**
     * Container used to load accounts
     */
    private AccountContainerInterface $accountContainer;

    /**
     * Class constructor
     *
     * @param EntityDefinition $def The definition of this type of object
     * @param EntityLoader $entityLoader The loader for a specific entity
     * @param AccountContainerInterface $accountContainer Container used to load accounts
     */
    public function __construct(EntityDefinition $def, EntityLoader $entityLoader, AccountContainerInterface $accountContainer)
    {
        $this->entityLoader = $entityLoader;
        $this->accountContainer = $accountContainer;
        parent::__construct($def);
    }
}
